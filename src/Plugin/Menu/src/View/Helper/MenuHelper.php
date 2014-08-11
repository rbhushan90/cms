<?php
/**
 * Licensed under The GPL-3.0 License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since	 2.0.0
 * @author	 Christopher Castro <chris@quickapps.es>
 * @link	 http://www.quickappscms.org
 * @license	 http://opensource.org/licenses/gpl-3.0.html GPL-3.0 License
 */
namespace Menu\View\Helper;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\ORM\Entity;
use Cake\Routing\Router;
use Cake\Utility\Hash;
use Cake\View\Helper;
use Cake\View\Helper\StringTemplateTrait;
use Cake\View\View;
use Menu\Utility\Breadcrumb;
use QuickApps\View\Helper\AppHelper;

/**
 * Menu helper.
 *
 * Renders nested database records into a well formated `<ul>` menus
 * suitable for HTML pages.
 */
class MenuHelper extends AppHelper {

	use StringTemplateTrait;

/**
 * Default configuration for this class.
 *
 * - `formatter`: Callable method used when formating each item.
 * - `beautify`: Set to true to "beautify" the resulting HTML, compacted HTMl will
 *    be returned if set to FALSE. You can set this option to a string compatible with 
 *    [htmLawed](http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/htmLawed_README.htm) library.
 *    e.g: `2s0n`. Defaults to FALSE (compact).
 * - `dropdown`: Set to true to automatically apply a few CSS styles for creating a
 *    "Dropdown" menu. Defaults to FALSE. This option is useful when rendering
 *    Multi-level menus, such as site's "main menu", etc.
 * - `activeClass`: CSS class to use when an item is active (its URL matches current URL).
 * - `firstItemClass`: CSS class for the first item.
 * - `lastItemClass`: CSS class for the last item.
 * - `hasChildrenClass`: CSS class to use when an item has children.
 * - `split`: Split menu into multiple root menus (multiple UL's). Must be an integer,
 *    or false for no split (by default).
 * - `breadcrumbGuessing`: Mark an item as "active" if its URL is on the breadcrumb stack.
 *    Default to true.
 * - `templates`: HTML templates used when formating items.
 *   - `div`: Template of the wrapper element which holds all menus when using `split`.
 *   - `root`: Top UL/OL menu template.
 *   - `parent`: Wrapper which holds children of a parent node.
 *   - `child`: Template for child nodes (leafs).
 *   - `link`: Template for link elements.
 *
 * ## Example:
 *
 * This example shows where each template is used when rendering a menu.
 *
 *     <div> // div template (only if split > 1)
 *         <ul> // root template (first part of split menu)
 *             <li> // child template
 *                 <a href="">Link 1</a> // link template
 *             </li>
 *             <li> // child template
 *                 <a href="">Link 2</a> // link template
 *                 <ul> // parent template
 *                     <li> // child template
 *                         <a href="">Link 2.1</a> // link template
 *                     </li>
 *                     <li> // child template
 *                         <a href="">Link 2.2</a> // link template
 *                     </li>
 *                     ...
 *                 </ul>
 *             </li>
 *             ...
 *         </ul>
 *
 *         <ul> // root template (second part of split menu)
 *             ...
 *         </ul>
 *
 *         ...
 *     </div>
 *
 * @var array
 */
	protected $_defaultConfig = [
		'formatter' => null,
		'beautify' => false,
		'dropdown' => false,
		'activeClass' => 'active',
		'firstClass' => 'first-item',
		'lastClass' => 'last-item',
		'hasChildrenClass' => 'has-children',
		'split' => false,
		'breadcrumbGuessing' => true,
		'templates' => [
			'div' => '<div{{attrs}}>{{content}}</div>',
			'root' => '<ul{{attrs}}>{{content}}</ul>',
			'parent' => '<ul{{attrs}}>{{content}}</ul>',
			'child' => '<li{{attrs}}>{{content}}{{children}}</li>',
			'link' => '<a href="{{url}}"{{attrs}}><span>{{content}}</span></a>',
		]
	];

/**
 * Constructor.
 *
 * @param View $View The View this helper is being attached to
 * @param array $config Configuration settings for the helper
 */
	public function __construct(View $View, $config = array()) {
		if (empty($config['formatter'])) {
			$this->_defaultConfig['formatter'] = function ($entity, $info) {
				return $this->formatter($entity, $info);
			};
		}
		parent::__construct($View, $config);
	}

/**
 * Renders a nested menu.
 *
 * This methods renders a HTML menu using a `threaded` result set:
 *
 *     // In controller:
 *     $this->set('links', $this->Links->find('threaded'));
 *
 *     // In view:
 *     echo $this->Menu->render('links');
 *
 * ### Options:
 *
 * You can pass an associative array `key => value`.
 * Any `key` not in `$_defaultConfig` will be treated as an additional attribute
 * for the top level UL (root). If `key` is in `$_defaultConfig` it will temporally
 * overwrite default configuration parameters:
 *
 * - `formatter`: Callable method used when formating each item.
 * - `activeClass`: CSS class to use when an item is active (its URL matches current URL).
 * - `firstItemClass`: CSS class for the first item.
 * - `lastItemClass`: CSS class for the last item.
 * - `hasChildrenClass`: CSS class to use when an item has children.
 * - `split`: Split menu into multiple root menus (multiple UL's)
 * - `templates`: The templates you want to use for this menu. Any templates
 *    will be merged on top of the already loaded templates. This option can
 *    either be a filename in App/Config that contains the templates you want
 *    to load, or an array of templates to use.
 *
 * @param array|\Cake\Collection\Collection $items Nested items to render, given
 * as a query result set or as an array list
 * @param array $options An array of HTML attributes and options
 * @return string HTML
 */
	public function render($items, $options = []) {
		$this->alter('MenuHelper.render', $items, $options);

		if (!empty($options['templates']) && is_array($options['templates'])) {
			$this->templates($options['templates']);
			unset($options['templates']);
		}

		$out = '';
		$attrs = [];

		foreach ($options as $key => $value) {
			if (isset($this->_defaultConfig[$key])) {
				$this->config($key, $value);
			} else {
				$attrs[$key] = $value;
			}
		}

		$this->countItems($items);

		if (intval($this->config('split')) > 1) {
			$arrayItems = ($items instanceof \Cake\ORM\Entity) ? $items->toArray() : (array)$items;
			$count = count($arrayItems);
			$size = round($count / intval($this->config('split')));
			$chunk = array_chunk($arrayItems, $size);
			$i = 0;

			foreach ($chunk as $menu) {
				$i++;
				$out .=	$this->formatTemplate('parent', [
					'attrs' => $this->templater()->formatAttributes(['class' => "menu-part part-{$i}"]),
					'content' => $this->_render($menu, $this->config('formatter'))
				]);
			}

			$out = $this->formatTemplate('div', [
				'attrs' => $this->templater()->formatAttributes($attrs),
				'content' => $out,
			]);
		} else {
			$out .= $this->formatTemplate('root', [
				'attrs' => $this->templater()->formatAttributes($attrs),
				'content' => $this->_render($items)
			]);
		}

		if ($this->config('beautify')) {
			$tidy = is_bool($this->config('beautify')) ? '1t0n' : $this->config('beautify');
			include_once Plugin::classPath('Menu') . 'Lib/htmLawed.php';
			$out = htmLawed($out, compact('tidy'));
		}

		$this->_clear();
		return $out;
	}

/**
 * Default callable method (see formatter option).
 *
 * ### Valid options are:
 *
 * - `templates`: Array of templates indexed as `templateName` => `templatePattern`.
 *    Temporally overwrites templates when rendering this item, after item is rendered
 *    templates are restored to previous values.
 * - `childAttrs`: Array of attributes for `child` template.
 *   - `class`: Array list of multiple CSS classes or a single string (will be merged
 *      with auto-generated CSS).
 * - `linkAttrs`: Array of attributes for the `link` template.
 *   - `class`: Same as childAttrs.
 *
 * ### Information argument
 *
 * The second argument `$info` holds a series of useful values when rendering each
 * item of the menu. This values are stored as `key` => `value` array.
 *
 * - `index` (integer): Position of current item.
 * - `total` (integer): Total number of items in the menu being rendered.
 * - `depth` (integer): Item depth within the tree structure.
 * - `hasChildren` (boolean): true|false
 * - `children` (string): HTML content of rendered children for this item.
 *    Empty if has no children.
 *
 * @param \Cake\ORM\Entity $item The item to render
 * @param array $info Array of useful information such as described above
 * @param array $options Additional options
 * @return string
 */
	public function formatter($item, array $info, array $options = []) {
		$this->alter('MenuHelper.formatter', $item, $info, $options);

		$options = Hash::merge([
			'templates' => [],
			'childAttrs' => ['class' => []],
			'linkAttrs' => ['class' => []],
		], $options);
		$config = $this->config();

		if (!empty($options['templates']) && is_array($options['templates'])) {
			$templatesBefore = $this->templates();
			$this->templates($options['templates']);
			unset($options['templates']);
		}

		if (!empty($options['childAttrs']['class'])) {
			if (is_string($options['childAttrs']['class'])) {
				$options['childAttrs']['class'] = [$options['childAttrs']['class']];
			}
		}

		if (!empty($options['linkAttrs']['class'])) {
			if (is_string($options['linkAttrs']['class'])) {
				$options['class']['class'] = [$options['linkAttrs']['class']];
			}
		}

		$childAttrs = $options['childAttrs'];
		$linkAttrs = $options['linkAttrs'];

		if ($info['index'] === 1) {
			$childAttrs['class'][] = $config['firstClass'];
		}

		if ($info['index'] === $info['total']) {
			$childAttrs['class'][] = $config['lastClass'];
		}

		if ($info['hasChildren']) {
			$childAttrs['class'][] = $config['hasChildrenClass'];
			if ($this->config('dropdown')) {
				$childAttrs['class'][] = 'dropdown';
				$linkAttrs['data-toggle'] = 'dropdown';
			}
		}

		if (!empty($item->description)) {
			$linkAttrs['title'] = $item->description;
		}

		if (!empty($item->target)) {
			$linkAttrs['target'] = $item->target;
		}

		if ($info['active']) {
			$childAttrs['class'][] = $config['activeClass'];
			$linkAttrs['class'][] = $config['activeClass'];
		}

		if (!empty($options['childAttrs'])) {
			$childAttrs = Hash::merge($childAttrs, $options['childAttrs']);
		}

		if (!empty($options['linkAttrs'])) {
			$linkAttrs = Hash::merge($linkAttrs, $options['linkAttrs']);
		}
	
		$childAttrs['class'] = array_unique($childAttrs['class']);
		$linkAttrs['class'] = array_unique($linkAttrs['class']);
		$childAttrs = $this->templater()->formatAttributes($childAttrs);
		$linkAttrs = $this->templater()->formatAttributes($linkAttrs);
		$return = $this->formatTemplate('child', [
			'attrs' => $childAttrs,
			'content' => $this->formatTemplate('link', [
				'url' => $this->_url($item->url),
				'attrs' => $linkAttrs,
				'content' => $item->title,
			]),
			'children' => $info['children'],
		]);

		if (isset($templatesBefore)) {
			$this->templates($templatesBefore);
		}

		return $return;
	}

/**
 * Counts items in menu.
 *
 * @param \Cake\ORM\Query $items
 * @return integer
 */
	public function countItems($items) {
		if ($this->_count) {
			return $this->_count;
		}

		$this->_count($items);
		return $this->_count;
	}

/**
 * Restores the default values built into MenuHelper.
 *
 * @return void
 */
	public function resetTemplates() {
		$this->templates($this->_defaultConfig['templates']);
	}

/**
 * Internal method to recursively generate the menu.
 *
 * @param \Cake\ORM\Query $items
 * @param integer $depth
 * @return string HTML
 */
	protected function _render($items, $depth = 0) {
		$content = '';
		$formatter = $this->config('formatter');

		foreach ($items as $item) {
			$children = '';
			$item = is_array($item) ? new Entity($item) : $item;

			if ($item->has('children') && !empty($item->children) && $item->expanded) {
				$children = $this->formatTemplate('parent', [
					'attrs' => $this->templater()->formatAttributes([
						'class' => ($this->config('dropdown') ? 'dropdown-menu multi-level' : ''),
						'role' => 'menu'
					]),
					'content' => $this->_render($item->children, $depth + 1)
				]);
			}

			$this->_index++;
			$info = [
				'index' => $this->_index,
				'total' => $this->_count,
				'active' => $this->_isActive($item),
				'depth' => $depth,
				'hasChildren' => !empty($children),
				'children' => $children,
			];
			$content .= $formatter($item, $info);
		}

		return $content;
	}

/**
 * Returns a safe URL string for later use on HtmlHelper.
 * 
 * @param string|array $url URL given as string or an array compatible
 * with `Router::url()`
 * @return string
 */
	public function _url($url) {
		static $locales = null;

		if (empty($locales)) {
			$locales = implode('|', 
				array_map('preg_quote', 
					array_keys(
						quickapps('languages')
					)
				)
			);
		}

		if (
			option('url_locale_prefix') &&
			is_string($url) &&
			str_starts_with($url, '/') &&
			!preg_match('/^\/(' . $locales . ')/', $url)
		) {
			$locale = Configure::read('Config.language');
			$url = "/{$locale}{$url}";
			$full = true;
		} else {
			$full = false;
		}

		try {
			$url = Router::url($url, $full);
		} catch (\Exception $e) {
			$url = '';
		}

		return $url;
	}

/**
 * Checks if the given item should be marked as active.
 *
 * `$item->url` property must exists, and can be either:
 *
 * - A string representing an external or internal URL (all internal links must
 *   starts with "/"). e.g. `/user/login`
 * - An array compatible with \Cake\Routing\Router::url().
 *   e.g. `['controller' => 'users', 'action' => 'login']`
 *
 * Both examples are equivalent.
 *
 * @param \Cake\ORM\Entity $item
 * @return boolean
 */
	protected function _isActive($item) {
		switch ($item->activation) {
			case 'any':
				return $this->_urlMatch($item->active);
			case 'none':
				return !$this->_urlMatch($item->active);
			case 'php':
				return php_eval($item->active, [
					'view', &$this->_View,
					'item', &$item,
				]) === true;
			case 'auto':
				default:
					try {
						$itemUrl = Router::url($item->url);
					} catch (\Exception $e) {
						$itemUrl = false;
					}

					// external link
					if (empty($itemUrl) || $itemUrl[0] !== '/') {
						return ($itemUrl == env('REQUEST_URI'));
					}

					$itemUrl = str_replace_once($this->_View->request->base, '', $itemUrl);
					if (option('url_locale_prefix')) {
						if (!preg_match('/^\/' . $this->_localesPattern() . '/', $itemUrl)) {
							$itemUrl = '/' . Configure::read('Config.language') . $itemUrl;
						}
					}

					$isInternal =
						$itemUrl !== '/' &&
						str_ends_with($itemUrl, str_replace_once($this->_View->request->base, '', env('REQUEST_URI')));
					$isIndex =
						$itemUrl === '/' &&
						$this->_View->request->is('homePage');
					$isExact =
						str_replace('//', '/', "{$itemUrl}/") === str_replace('//', '/', "/{$this->_View->request->url}/");

					if ($this->config('breadcrumbGuessing')) {
						static $crumbs = null;
						if ($crumbs === null) {
							$crumbs = Breadcrumb::getUrls();
							foreach ($crumbs as &$crumb) {
								try {
									$crumb = Router::url($crumb);
								} catch (\Exception $e) {
									$crumb = '';
								}

								if (str_starts_with($crumb, $this->_View->request->base)) {
									$crumb = str_replace_once($this->_View->request->base, '', $crumb);
								}

								if (option('url_locale_prefix')) {
									if (!preg_match('/^\/' . $this->_localesPattern() . '/', $crumb)) {
										$crumb = '/' . Configure::read('Config.language') . $crumb;
									}
								}
							}
						}

						$isInBreadcrumb = in_array($itemUrl, $crumbs);
						return ($isInternal || $isIndex || $isExact || $isInBreadcrumb);
					}

					return ($isInternal || $isIndex || $isExact); 
		}
	}

/**
 * Check if a path matches any pattern in a set of patterns.
 *
 * @param string $patterns String containing a set of patterns separated by \n,
 * \r or \r\n
 * @param mixed $path String as path to match. Or false to use current page URL
 * @return boolean TRUE if the path matches a pattern, FALSE otherwise
 */
	protected function _urlMatch($patterns, $path = false) {
		if (empty($patterns)) {
			return false;
		}

		$request = $this->_View->request;
		$path = !$path ? '/' . $request->url : $path;
		$patterns = explode("\n", $patterns);

		foreach ($patterns as &$p) {
			$p = $this->_View->Html->url('/') . $p;
			$p = str_replace('//', '/', $p);
			$p = str_replace($request->base, '', $p);

			if (
				option('url_locale_prefix') &&
				!preg_match('/^\/' . $this->_localesPattern() . '\//', $p, $matches)
			) {
				$p = '/' . Configure::read('Config.language') . $p;
			}
		}

		$patterns = implode("\n", $patterns);

		// Convert path settings to a regular expression.
		// Therefore replace newlines with a logical or, /* with asterisks and "/" with the frontpage.
		$to_replace = array(
			'/(\r\n?|\n)/', // newlines
			'/\\\\\*/', // asterisks
			'/(^|\|)\/($|\|)/' // front '/'
		);

		$replacements = array(
			'|',
			'.*',
			'\1' . preg_quote($this->_View->Html->url('/'), '/') . '\2'
		);

		$patterns_quoted = preg_quote($patterns, '/');
		$regexps[$patterns] = '/^(' . preg_replace($to_replace, $replacements, $patterns_quoted) . ')$/';

		return (bool)preg_match($regexps[$patterns], $path);
	}

/**
 * Internal method for counting items in menu.
 *
 * This method will ignore children if parent has been marked as `do no expand`.
 *
 * @param \Cake\ORM\Query $items
 * @return integer
 */
	protected function _count($items) {
		foreach ($items as $item) {
			$this->_count++;
			$item = is_array($item) ? new Entity($item) : $item;

			if ($item->has('children') && !empty($item->children) && $item->expanded) {
				$this->_count($item->children);
			}
		}
	}

	protected function _localesPattern() {
		$cacheKey = '_localesPattern';
		$cache = static::_cache($cacheKey);
		if ($cache) {
			return $cache;
		}

		$pattern = '(' . implode('|', 
			array_map('preg_quote', 
				array_keys(
					quickapps('languages')
				)
			)
		) . ')';
		return static::_cache($cacheKey, $pattern);
	}

/**
 * Clears all temporary variables used when rendering a menu, so they do not
 * interfere when rendering other menus.
 *
 * @return void
 */
	protected function _clear() {
		$this->_index = 0;
		$this->_count = 0;
		$this->config($this->_defaultConfig);
		$this->resetTemplates();
	}

}

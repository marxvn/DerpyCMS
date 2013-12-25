<?php
/**
 * DerpyCMS - A derpier CMS
 *
 * @author    Diftraku <diftraku(at)derpy.me>
 * @copyright 2013 Diftraku
 * @link      https://github.com/Diftraku/DerpyCMS
 * @license   https://github.com/Diftraku/DerpyCMS/wiki/License
 * @version   Development
 * @package   DerpyCMS
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace DerpyCMS;

/**
 * Interface CRUD_Interface
 *
 * @package DerpyCMS
 */
interface CRUD_Interface {
	/**
	 * @param null $id
	 */
	public function __construct($id = null);

	/**
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	public function set($key, $value);

	/**
	 * @param $key
	 * @return mixed
	 */
	public function get($key);

	/**
	 * @return mixed
	 */
	public function fetch();

	/**
	 * @return mixed
	 */
	public function save();

	/**
	 * @return mixed
	 */
	public function destroy();

	/**
	 * @param $key
	 * @return mixed
	 */
	public function has($key);

	/**
	 * @return mixed
	 */
	public function isNew();

	/**
	 * @return mixed
	 */
	public function hasChanged();

	/**
	 *
	 */
	public function __destruct();
}

/**
 * Class Model
 * This model takes inspiration from Backbone.js, implementing robust CRUD support
 * while tracking changes to the model and syncing them to the server if needed.
 *
 * @package DerpyCMS
 */
abstract class Model implements CRUD_Interface {
	/**
	 * @var null
	 */
	protected $id = null;
	/**
	 * @var array
	 */
	protected $data = array();
	/**
	 * @var array
	 */
	protected $changed = array();

	/**
	 * @param null $id
	 */
	public function __construct($id = null) {
		if (!is_null($id)) {
			$this->id = $id;
			$this->fetch();
		}
	}

	/**
	 * @param      $key
	 * @param null $value
	 * @return mixed
	 */
	public function set($key, $value = null) {
		if (is_null($key)) {
			return;
		}
		$changed = false;
		if ($this->has($key)) {
			$changed = $this->get($key) !== $value;
		}
		else {
			$changed = true;
		}
		if ($changed) {
			$this->changed[$key] = $value;
		}

		return;
	}

	/**
	 * @param $key
	 * @return null
	 */
	public function get($key) {
		if ($key == 'id') {
			return $this->id;
		}
		elseif (array_key_exists($key, $this->changed)) {
			return $this->changed[$key];
		}
		elseif (array_key_exists($key, $this->data)) {
			return $this->data[$key];
		}
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function has($key) {
		return array_key_exists($key, $this->changed) || array_key_exists($key, $this->data);
	}

	/**
	 * @return bool
	 */
	public function isNew() {
		return is_null($this->id);
	}

	/**
	 * @param null $key
	 * @return bool
	 */
	public function hasChanged($key = null) {
		if (is_null($key)) {
			return !empty($this->changed);
		}

		return array_key_exists($key, $this->changed);
	}

	/**
	 * @return mixed
	 */
	abstract public function fetch();

	/**
	 * @return mixed
	 */
	abstract public function save();

	/**
	 * @return mixed
	 */
	abstract public function destroy();

	/**
	 * @TODO Do we even need this?
	 */
	public function __destruct() {
		return;
	}
}
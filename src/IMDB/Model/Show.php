<?php
/**
 * Created by PhpStorm.
 * User: ozberk
 * Date: 12/8/13
 * Time: 5:01 PM
 */

namespace IMDB\Model;


use Exception;
use IMDB\FileOperations\OperationsInterface;

/**
 * Class Show
 * @package IMDB\Model
 */
class Show
{
	protected $i_line_number;
	protected $o_file;
	protected $a_props = array(
		'lineNumber' => null,
		'showTitle' => null,
		'showYear' => null,
		'partYear' => null,
		'partTitle' => null,
		'partEpisodeNumber' => null,
		'partSeasonNumber' => null
	);

	/**
	 * Injects FileOperations object, and uses it for delete/edit and load purposes.
	 * @param OperationsInterface $o_file
	 * @param int $i_line
	 */
	public function __construct($o_file, $i_line = null)
	{
		$this->o_file = $o_file;
		if ($i_line > 0) {
			$this->loadByLineNumber($i_line);
		}
	}

	/**
	 * Load show object via its line number.
	 * @param $i_line_number
	 */
	public function loadByLineNumber($i_line_number)
	{
		$s_line = $this->o_file->getLine($i_line_number);
		$this->loadFromLine($s_line);
	}

	/**
	 * Parse a line into object
	 * @param $s_line
	 * @return bool
	 * @throws Exception
	 */
	public function loadFromLine($s_line)
	{
		preg_match(
			'/\s+(.*?)\t\"?(.*?)\"? \((.*?)\)(?:\s?\{+(.*?)(?: ?\(\#(\d{1,2}).(\d{1,2})\))?\}+)?.*\t(\d{0,4})/',
			$s_line,
			$a_matches
		);

		if (!isset($a_matches[1])) {
			throw new Exception('"' . $s_line . '" invalid format!');
		}

		$this->set('lineNumber', $a_matches[1]);
		$this->set('showTitle', $a_matches[2]);
		$this->set('showYear', $a_matches[3]);
		if ($a_matches[4]) {
			$this->set('partYear', $a_matches[7]);
			$this->set('partTitle', $a_matches[4]);
			$this->set('partEpisodeNumber', $a_matches[6]);
			$this->set('partSeasonNumber', $a_matches[5]);
		}

		if (!$this->isValid()) {
			throw new Exception($s_line . ' could not be parsed correctly!');
		}

		return true;
	}

	/**
	 * Simply checks that if the object has 3 common fields that all lines should have
	 * @return bool
	 */
	protected function isValid()
	{
		return $this->get('lineNumber') && $this->get('showTitle') && $this->get('showYear');
	}

	/**
	 * Sets a property of Show model.
	 * @param string $s_variable_name
	 * @param mixed $m_value
	 * @throws Exception
	 */
	protected function set($s_variable_name, $m_value)
	{
		if (!array_key_exists($s_variable_name, $this->a_props)) {
			throw new Exception($s_variable_name . ' is not valid property name.');
		}

		$this->a_props[$s_variable_name] = $m_value;
	}

	/**
	 * Returns the value of a property
	 * @param $s_variable_name
	 * @return mixed
	 * @throws \Exception
	 */
	public function get($s_variable_name)
	{
		if (!array_key_exists($s_variable_name, $this->a_props)) {
			throw new Exception($s_variable_name . ' is not valid property name.');
		}

		return $this->a_props[$s_variable_name];
	}

	/**
	 * Returns the class properties as array. strlen map is for removing null only props.
	 * @return array
	 */
	public function toArray()
	{
		return array_filter($this->a_props, 'strlen');
	}

	public function delete()
	{
		if (!$this->isValid()) {
			throw new Exception('Object is not loaded yet.');
		}
		return $this->o_file->deleteLine($this->get('lineNumber'));
	}
}
<?php
/**
 * Created by PhpStorm.
 * User: ozberk
 * Date: 12/8/13
 * Time: 5:38 PM
 */

namespace IMDB\FileOperations;


/**
 * Class OperationsInterface
 * @package IMDB\FileOperations
 */
interface OperationsInterface
{
	/**
	 * Saves the remote file in the needed schema
	 * @param $s_remote_path
	 * @return mixed
	 */
	public function saveFormatted($s_remote_path);

	/**
	 * Sorts by the given column
	 * @param $i_column
	 * @param null $s_target_path
	 * @return mixed
	 */
	public function sort($i_column, $s_target_path = null);

	/**
	 * Returns the result set by offset and limit
	 * @param $i_offset
	 * @param $i_limit
	 * @return mixed
	 */
	public function get($i_offset, $i_limit);

	/**
	 * Returns specific line
	 * @param $i_line_number
	 * @return mixed
	 */
	public function getLine($i_line_number);

	public function deleteLine($i_line_number);

	/**
	 * Returns results set of the lines which consists the keyword.
	 * @param $s_keyword
	 * @param $i_offset
	 * @param $i_limit
	 * @return mixed
	 */
	public function getByKeyword($s_keyword, $i_offset, $i_limit);
	public function getSize();
	public function getFilePath();

}
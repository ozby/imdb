<?php
/**
 * Created by PhpStorm.
 * User: ozberk
 * Date: 12/8/13
 * Time: 5:42 PM
 */

namespace IMDB\FileOperations;

use Exception;
use Symfony\Component\Process\Process;

class Unix implements OperationsInterface
{
	protected $s_file_path;
	protected $i_file_row_count;
	protected $i_startLine = 16;

	public function __construct($s_file_path = null)
	{
		if ($s_file_path) {
			$this->setFilePath($s_file_path);
		}
	}

	public function saveFormatted($s_remote_file_path, $s_target_path = null)
	{
		if (!$s_target_path) {
			$s_target_path = $this->getFilePath();
		}
		$o_process = new Process('curl -XGET ftp://ftp.fu-berlin.de/pub/misc/movies/database/movies.list.gz | gunzip -f | cat -n > ' . $s_target_path);
		$o_process->run();

		if ($o_process->isSuccessful()) {
			$this->setFilePath($s_target_path);

			return true;
		}

		return false;
	}

	/**
	 * Sorts by the given column
	 * @param $i_column
	 * @param null $s_target_path
	 * @return mixed
	 */
	public function sort($i_column, $s_target_path = null)
	{
		$s_sorted_local_file_path = $s_target_path ? : '/tmp/movies_sorted.list';
		$o_process = new Process('sort -t $\'\t\' -k' . $i_column . ' -n ' . $this->getFilePath(
		) . '> ' . $s_sorted_local_file_path);
		$o_process->run();

		if (!$o_process->isSuccessful()) {
			throw new Exception('Sorting error : ' . $o_process->getErrorOutput());
		}

		return $s_sorted_local_file_path;
	}

	/**
	 * Get result set via offset,limit
	 * @param $i_offset
	 * @param $i_limit
	 * @return array
	 * @throws \Exception
	 */
	public function get($i_offset, $i_limit)
	{
		$i_start = $i_offset + $this->getStartLine();

		$o_process = new Process('tail -n +' . $i_start . ' ' . $this->getFilePath() . ' | head -n ' . $i_limit);
		$o_process->run();

		if (!$o_process->isSuccessful()) {
			throw new Exception($o_process->getErrorOutput());
		}

		return explode("\n", utf8_encode($o_process->getOutput()));
	}

	/**
	 * Returns results set of the lines which consists the keyword.
	 * @param $s_keyword
	 * @param $i_offset
	 * @param $i_limit
	 * @return mixed
	 */
	public function getByKeyword($s_keyword, $i_offset, $i_limit)
	{
		$o_process = new Process('export LC_ALL=C; cat ' . $this->getFilePath(
		) . ' | grep -i "' . $s_keyword . '" | tail -n +' . $i_offset . ' | head -n ' . $i_limit);
		$o_process->run();

		if (!$o_process->isSuccessful()) {
			throw new Exception($o_process->getErrorOutput());
		}

		return explode("\n", utf8_encode($o_process->getOutput()));
	}

	/**
	 * Returns the specific line.
	 * @param $i_line_number
	 * @return bool|string
	 * @throws \Exception
	 */
	public function getLine($i_line_number)
	{
		if (!$i_line_number > 0) {
			return false;
		}
		if ($this->getStartLine() > $i_line_number) {
			throw new Exception('The given line is not show.');
		}
		$o_process = new Process('sed -n ' . $i_line_number . 'p ' . $this->getFilePath());
		$o_process->run();

		if (!$o_process->isSuccessful()) {
			throw new Exception($o_process->getErrorOutput());
		}

		return utf8_encode($o_process->getOutput());

	}

	/**
	 * Deletes the given line
	 * @param $i_line_number
	 * @return bool
	 * @throws \Exception
	 */
	public function deleteLine($i_line_number)
	{
		if (!$i_line_number > 0) {
			return false;
		}
		echo 'sed -i \'\' ' . $i_line_number . 'd ' . $this->getFilePath();
		$o_process = new Process('sed -i \'\' ' . $i_line_number . 'd ' . $this->getFilePath());
		$o_process->run();

		if (!$o_process->isSuccessful()) {
			throw new Exception($o_process->getErrorOutput());
		}

		return true;
	}

	protected function setSize($i_size)
	{
		$this->i_file_row_count = $i_size - $this->getStartLine();
	}

	public function getSize()
	{
		if ($this->s_file_path === null) {
			throw new Exception("File is not loaded.");
		}

		if ($this->i_file_row_count !== null) {
			return $this->i_file_row_count;
		}

		$o_process = new Process("wc -l " . $this->getFilePath());
		$o_process->run();
		$i_size = (int)$o_process->getOutput();
		$this->setSize($i_size);

		return $this->getSize();
	}

	public function getFilePath()
	{
		return $this->s_file_path;
	}

	public function setFilePath($s_file_path)
	{
		$this->i_file_row_count = null;
		$this->s_file_path = $s_file_path;
	}

	public function setStartLine($i_start_line)
	{
		$this->i_startLine = $i_start_line;
	}

	public function getStartLine()
	{
		return $this->i_startLine;
	}
} 
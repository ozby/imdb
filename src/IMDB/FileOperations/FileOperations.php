<?php
/**
 * Created by PhpStorm.
 * User: ozberk
 * Date: 12/8/13
 * Time: 5:45 PM
 */

namespace IMDB\FileOperations;


class FileOperations
{
	public static function create($s_type, $s_file_path=null)
	{
		if ($s_type === 'unix') {
			return new Unix($s_file_path);
		}

		return false;
	}
} 
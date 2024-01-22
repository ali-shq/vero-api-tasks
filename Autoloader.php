<?php

class Autoloader
{
	const FOLDERS = ['classes', 'classes/Exception'];


	public static function register()
	{

		spl_autoload_register(function ($class) {

			foreach (Autoloader::FOLDERS as $folder) {

				$file = "$folder/{$class}.php";
				if (file_exists($file)) {
					require_once $file;
					return true;
				}
				return false;
	
			}

		});
	}
}
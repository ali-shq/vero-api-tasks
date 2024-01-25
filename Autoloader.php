<?php

class Autoloader
{
	const FOLDERS = ['classes', 'classes/Exceptions', 'classes/Controllers', 'classes/Models'];


	public static function register()
	{

		spl_autoload_register(function ($class) {

			foreach (Autoloader::FOLDERS as $folder) {

				$file = "$folder/{$class}.php";
				if (file_exists($file)) {
					require $file;
					return true;
				}			
			}
			return false;
	

		});
	}
}
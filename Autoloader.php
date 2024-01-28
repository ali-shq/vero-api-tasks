<?php

class Autoloader
{
	const FOLDERS = ['classes', 'classes/Exceptions', 'classes/Controllers', 'classes/Models'];

	
	/**
	 * register scan the Autoloader::FOLDERS for the requested class, ClassName.php is the file searched
	 * for a ClassName class
	 *
	 * @return void
	 */
	public static function register() : void
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
<?php

/**
 * DocsController a controller that reads the phpDocs of the classes defined within the App
 * and outputs the result as text
 */
class DocsController extends Controller
{

        
    /**
     * getResponse overwrites the basic getResponse method to provide a single response regardless of the httpVerb
     *
     * @param  string $httpVerb not used
     * @param  array $request not used
     * @param  mixed $key if it is the name of an existing classm only that class' documentation will be shown
     * @return void
     */
    public function getResponse(string $httpVerb, array $request, $key = null) : void 
    {
        header('Content-Type: text; charset=utf-8');

        if (class_exists($key ?? '-')) {

            $this->docClass($key);

            return;
        }
        
        foreach (Autoloader::FOLDERS as $folder) {

            foreach (Utils::getFolderClasses($folder) as $class) {

                $this->docClass($class, !Env::$isDeveplopment);

            }

        }

    }


    
    /**
     * getModifiers returns a string with the method modifiers
     *
     * @param  ReflectionMethod $method
     * @return string
     */
    protected function getModifiers(ReflectionMethod $method) : string
    {
        return implode(' ', Reflection::getModifierNames($method->getModifiers()));
    }



    protected function docClass(string $className, $publicOnly = false) 
    {

        echo "CLASS: $className\n\n";

        $ref = new ReflectionClass($className);

        $classComment = $ref->getDocComment();

        if ($classComment) {
            echo "$classComment\n";
        }

        
        $propertyWithCommentHeader = "\n  Some properties of interest:\n";

        $propHeaderEchoed = false;

        foreach ($ref->getProperties($publicOnly ? ReflectionMethod::IS_PUBLIC : null) as $prop) {

            $refProperty = $ref->getProperty($prop->name);

            $comment = $refProperty->getDocComment();

            if (!$comment) {

                continue;

            }

            if (!$propHeaderEchoed) {

                echo $propertyWithCommentHeader;

                $propHeaderEchoed = true;

            }

            echo "   $prop->name \n";

            echo "     $comment\n";
    
        }



        echo "\n  Methods\n";

        foreach ($ref->getMethods($publicOnly ? ReflectionMethod::IS_PUBLIC : null) as $method) {

            $refMethod = $ref->getMethod($method->name);

            $modifier  = $this->getModifiers($refMethod);

            echo "   $modifier $method->name \n";

            $methodComment = $refMethod->getDocComment();

            if ($methodComment) {
                echo "     $methodComment\n";
            }
    

        }

        echo "\n\n\n";


    }



}
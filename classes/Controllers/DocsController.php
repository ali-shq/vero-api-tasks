<?php

class DocsController extends Controller
{


    public function get($request) : array {

        $ref = new ReflectionClass(Utils::class);

        $docs = ['header' => $ref->getDocComment()];

        foreach ($ref->getMethods() as $method) {
            $docs['methods'][] = $ref->getMethod($method->name)->getDocComment();
        }


        return $docs;
    }


}
<?php

class ConstructionStagesController extends Controller
{

    public function delete($request) 
    {

        $request['status'] = 'DELETED';

        return parent::edit($request);

    }

}
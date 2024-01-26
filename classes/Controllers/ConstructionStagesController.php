<?php

class ConstructionStagesController extends Controller
{


    public function delete($request) 
    {

        $request['status'] = 'DELETED';

        return parent::edit($request);

    }



    public function edit($request) : array
    {
        $this->updateDurationOnEdit($request);

        return parent::edit($request);
    }



    public function add($request) : array
    {
        $this->model->addDefaultValues($request);

        $this->model->checkValidations($request);

        $request['duration'] = $this->calculateDuration($request);

        return parent::add($request);
    }



    private function updateDurationOnEdit(&$request) 
    {
        $durationAffectingFields = array_flip(['startDate', 'endDate', 'durationUnit']);

        $durationAffectingRequest = array_intersect_key($request, $durationAffectingFields);

        if ($durationAffectingRequest == []) {

            return;

        }

        $id = $request[$this->model->id] ?? null;

        $oldData = $this->model->getById($id)[0];

        $durationAffectingRequest += $oldData;

        $request['duration'] = $this->calculateDuration($durationAffectingRequest);

    }



    private function calculateDuration($request) : ?float
    {
        
        $diffInHours = Utils::dateDiffInHours($request['endDate'] ?? null, $request['startDate']);

        if ($diffInHours === null) {

            return null;

        }

        
        $factor = 1;

        $durationUnit = $request['durationUnit'];

        if ($durationUnit == 'DAYS') {

            $factor = 24;

        } elseif ($durationUnit == 'WEEKS') {

            $factor = 24 * 7;

        }

        return $diffInHours / $factor;
    }

}
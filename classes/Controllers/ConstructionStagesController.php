<?php

/**
 * ConstructionStagesController the controller class for the construction stages
 */
class ConstructionStagesController extends Controller
{

    
    /**
     * delete overwrites the default delete method to instead do an updated with DELETED status
     * and returns back the "deleted" record
     * @param  array $request
     * @return array
     */
    public function delete($request) : array
    {

        $request['status'] = 'DELETED';

        return parent::edit($request);

    }


    
    /**
     * edit overwrites the default edit method only by adding the calculated duration
     *
     * @param  array $request
     * @return array
     */
    public function edit(array $request) : array
    {
        $this->addDurationOnEdit($request);

        return parent::edit($request);
    }


    
    /**
     * add overwrites the default Controller::add method to add the calculated duration into the request
     *
     * @param  array $request
     * @return array
     */
    public function add(array $request) : array
    {
        $this->model->addDefaultValues($request);

        $this->model->checkValidations($request);

        $request['duration'] = $this->calculateDuration($request);

        return parent::add($request);
    }


    
    /**
     * addDurationOnEdit helper method that adds the duration key with the new value to the request array
     * unsets the key if the duration should have not changed given the request 
     *
     * @param  array $request
     * @return void
     */
    private function addDurationOnEdit(array &$request) : void
    {
        $durationAffectingFields = array_flip(['startDate', 'endDate', 'durationUnit']);

        $durationAffectingRequest = array_intersect_key($request, $durationAffectingFields);

        if ($durationAffectingRequest == []) {

            unset($request['duration']);

            return;

        }

        $id = $request[$this->model->id] ?? null;

        $oldData = $this->model->getById($id)[0];

        $durationAffectingRequest += $oldData;

        $request['duration'] = $this->calculateDuration($durationAffectingRequest);

    }


    
    /**
     * calculateDuration calculates the value of the duration, given startDate and endDate and the durationUnit
     *
     * @param  array $request
     * @return float
     */
    private function calculateDuration(array $request) : ?float
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
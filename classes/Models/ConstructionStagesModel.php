<?php

class ConstructionStagesModel extends Model
{

	public function __construct()
	{
		$addProperties = [	
							'name',
							'startDate',
							'endDate',
							'duration',
							'durationUnit',
							'color',
							'externalId',
							'status'	
						];


		$this->allProperties = array_merge($this->allProperties, $addProperties);



		$this->overWriteGetValues['startDate'] = Utils::standartDateTime(...);

		$this->overWriteGetValues['endDate'] = Utils::standartDateTime(...);

		parent::__construct();
		
	}
}
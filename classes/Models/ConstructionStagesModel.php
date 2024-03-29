<?php

/**
 * ConstructionStagesModel the model class for the construction stages
 */
class ConstructionStagesModel extends Model
{

	
	/**
	 * __construct does the setup of the ConstructionStagesModel
	 * from defining the "properties" to adding default values and the needed validations
	 * @return void
	 */
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


		$this->defaultValues['durationUnit'] = 'DAYS';

		$this->defaultValues['status'] = 'NEW';


		$this->overWriteGetValues['startDate'] = Utils::standartDateTime(...);

		$this->overWriteGetValues['endDate'] = Utils::standartDateTime(...);


		$this->validations = Validation::generateRequired(['name', 'startDate', 'durationUnit', 'status']);

		$this->validations[] = Validation::generateMaxLength('name', 255);

		$this->validations[] = Validation::generateValidDate('startDate');

		$this->validations[] = Validation::generateValidDate('endDate');

		
		$this->validations[] = $this->generateEndDateValidation();
	

		$this->validations[] = Validation::generateInList('status', ['NEW', 'PLANNED', 'DELETED']);

		$this->validations[] = Validation::generateInList('durationUnit', ['HOURS', 'DAYS', 'WEEKS']);

		$this->validations[] = Validation::generateValidColor('color');
		
		$this->validations[] = Validation::generateMaxLength('externalId', 255);


		parent::__construct();
		
	}

	
	/**
	 * generateEndDateValidation
	 * 
	 * returns a Validation instance that check's whether endDate is greater than startDate
	 *
	 * @return Validation
	 */
	private function generateEndDateValidation() : Validation
	{
		$validEndDate = function($request) {
			return	!isset($request['endDate']) || !isset($request['startDate']) 		  ||
					!date_create_from_format(Env::$dateTimeFormat, $request['endDate'])   ||
					!date_create_from_format(Env::$dateTimeFormat, $request['startDate']) ||
					$request['endDate'] > $request['startDate']; 
		};

		$validEndDateMessage = GetMessage::msg(Message::END_NOT_GREATER_THAN_START, 'endDate', 'startDate');

		return new Validation($validEndDate, $validEndDateMessage);

	}
}
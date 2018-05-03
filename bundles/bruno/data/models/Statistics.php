<?php

namespace bundles\bruno\data\models;

use Illuminate\Database\Eloquent\Model;
use \bundles\bruno\data\models\ModelBruno;
use \bundles\bruno\data\models\data\Question;
use \bundles\bruno\data\models\data\Answer;

class Statistics extends Model {

	protected $connection = 'data';

	protected $table = 'statistics';
	protected $morphClass = 'statistics';

	protected $primaryKey = 'id';

	public $timestamps = false;

////////////////////////////////////////////

// No relation needed

////////////////////////////////////////////

	//Add these functions to insure that nobody can make them disappear
	public function delete(){ return false; }
	public function restore(){ return false; }

////////////////////////////////////////////

	public function save(array $options = array()){
		$time_ms = ModelBruno::getMStime();
		if(!isset($this->id)){
			$this->c_at = $time_ms;
			if(!isset($this->md5)){
				$this->md5 = md5(uniqid('', true));
			}
		}
		$this->u_at = $time_ms;
		return parent::save($options);
	}

	public static function unlock($session_id, $question_id){
		if($session_id == 0){
			//We don't create any statistics for preview
			return false;
		}
		$statistics = Statistics::Where('session_id', $session_id)->where('question_id', $question_id)->first();
		if(!$statistics){
			$statistics = new Statistics;
			$statistics->session_id = $session_id;
			$statistics->question_id = $question_id;
			$statistics->save();
			if($question = Question::Where('id', $question_id)->first(array('id', 'style', 'number'))){
				$statistics->style = $question->style;
				if($question->style==1 || $question->style==2){
					$statistics->number = $question->number;
				}
				$answers = false;
				if($question->style==2){ //Pictures
					$answers = Answer::Where('parent_id', $question->id)
						->whereNotNull('file_id')
						->take(6)
						->orderBy('number')
						->get(array('id', 'number'));
				} else {
					$answers = Answer::Where('parent_id', $question->id)
						->where(function($query) {
							$query
								->whereNotNull('file_id')
								->orWhere('title', '!=', '');
						})
						->take(6)
						->orderBy('number')
						->get(array('id', 'number'));
				}
				if($answers){
					//Set to 0 the used columns, and keep to null all unsed
					foreach ($answers as $answer) {
						$letter = ModelBruno::numToAplha($answer->number);
						$statistics->$letter = 0;
						if($question->style==4){
							$statistics->{'t_'.$letter} = 0;
						}
					}
				}
				$statistics->save();
			}
		}
		usleep(50000); //This sleep insure to not double the save of statistics
		return $statistics;
	}

}

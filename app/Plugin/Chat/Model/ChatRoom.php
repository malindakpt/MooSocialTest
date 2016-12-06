<?php
App::uses('ChatAppModel', 'Chat.Model');
/**
 * OauthAccessToken Model
 *
 * @property User $User
 */
class ChatRoom extends ChatAppModel {
   public $recursive = 2;
/**
 * Primary key field
 *
 * @var string
 */
	public $primaryKey = 'id';

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'id';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'id' => array(
			'notEmpty' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		)
	);

	public $hasMany = array(
		'ChatRoomsMember' => array(
			'className' => 'ChatRoomsMember',
			'foreignKey' => 'room_id',
			'dependent' => true
		)
	);
	//public $actsAs = array('Containable');
}

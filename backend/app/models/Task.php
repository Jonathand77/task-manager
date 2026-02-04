<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class Task extends Model
{
    public $id;
    public $user_id;
    public $title;
    public $description;
    public $status;
    public $created_at;
    public $updated_at;

    public function initialize()
    {
        $this->setSource('tasks');
        $this->belongsTo('user_id', 'App\Models\User', 'id', [
            'alias' => 'user'
        ]);
    }

    public function beforeCreate()
    {
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
        
        if (empty($this->status)) {
            $this->status = 'pending';
        }
    }

    public function beforeUpdate()
    {
        $this->updated_at = date('Y-m-d H:i:s');
    }

    /**
     * Validaciones
     */
    public function validation()
    {
        $validator = new \Phalcon\Validation();

        $validator->add('title', new \Phalcon\Validation\Validator\StringLength([
            'min'     => 3,
            'max'     => 255,
            'message' => 'Title must be between 3 and 255 characters'
        ]));

        $validator->add('status', new \Phalcon\Validation\Validator\InclusionIn([
            'domain'  => ['pending', 'in_progress', 'done'],
            'message' => 'Status must be pending, in_progress or done'
        ]));

        return $this->validate($validator);
    }
}

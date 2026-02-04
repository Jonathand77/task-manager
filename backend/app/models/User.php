<?php

namespace App\Models;

use Phalcon\Mvc\Model;

class User extends Model
{
    public $id;
    public $email;
    public $password;
    public $name;
    public $created_at;
    public $updated_at;

    public function initialize()
    {
        $this->setSource('users');
    }

    public function beforeCreate()
    {
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
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
        $this->validate(new \Phalcon\Validation\Validator\Email([
            'attribute' => 'email',
            'message'   => 'Invalid email format'
        ]));

        $this->validate(new \Phalcon\Validation\Validator\Uniqueness([
            'attribute' => 'email',
            'message'   => 'Email already exists'
        ]));

        return !$this->validationHasFailed();
    }

    /**
     * Relación con tareas
     */
    public function getTasks()
    {
        return $this->hasMany('id', 'App\Models\Task', 'user_id');
    }
}

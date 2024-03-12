<?php
/**
 * @author ines
 * Le repository TaskRepository herite du repository de base ResourceRepository
 * Elle peut d'office executer les fonctions du ResourceRepository
 */
namespace App\Repositories;

use App\Repositories\ResourceRepository;

use App\Models\Contact;

class TaskRepository extends ResourceRepository
{
    public function __construct(Contact $contact)
    {
        $this->model = $contact;
    }

}
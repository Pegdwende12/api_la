<?php
/**
 * @author ines
 */
namespace App\Repositories;

abstract class ResourceRepository
{
    protected $model;

    /**
     * recupere le nombre d'instance du modele passeé en parametre
     * dans le model
     */
    public function getPaginate($n)
    {
        return $this->model->paginate($n);
    }

    /**
     * retourne toutes les instances du modele
     * enregistreé en BD
     */
    public function get($conditions = [])
    {
        $userId = auth()->id();

        // Ajoutez la condition user_id pour filtrer par utilisateur connecté
        $conditions['user_id'] = $userId;

        return $this->model->where($conditions)->get();
    }


    public function findByEmailAndUserId($email, $userId) {
        return $this->model->where('mail', $email)->where('user_id', $userId)->first();
    }

    /**
     * Enregistre un /de instance(s) 
     */
    public function store(Array $inputs)
    {
        
        return $this->model->create($inputs);
    }

    /**
     * Recupere une instance a partir de l'id
     */
    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }
	

    /**
     * Met a jour la table a partir de l'id recu
     */
    public function update($id, array $inputs)
    {
        // Récupérer la tâche par son ID
        $contact = $this->model->find($id);

        // Vérifier si la tâche existe
        if (!$contact) {
            throw new \Exception("La tâche avec l'ID $id n'a pas été trouvée.");
        }

        // Vérifier si l'utilisateur connecté est le créateur de la tâche
        if ($contact->user_id !== auth()->user()->id) {
            // L'utilisateur n'est pas autorisé à mettre à jour cette tâche
            throw new \Exception("Vous n'êtes pas autorisé à mettre à jour cette tâche.");
        }

        // Mise à jour des champs de la tâche
        $contact->update($inputs);

        // Retourne la tâche mise à jour
        return $contact;
    }



    /**
     * Supprime une instance de la table a partir de l'id recu
     */
    public function destroy($id)
    {
        $contact = $this->model->find($id);
        if ($contact->user_id !== auth()->user()->id) {
            // L'utilisateur n'est pas autorisé à supprimer cette tâche
            return response()->json(["Vous n'êtes pas autorisé à supprimer cette tâche."]);
        }

        $contact->delete();
    }


    //generer pdf
    public function genererPDF(){

    }

}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\TaskRepository;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;


class ContactController extends Controller
{
    //
    protected $taskRepository ;
    public function __construct(TaskRepository $taskrepository)
    {
        $this->taskRepository = $taskrepository;
    }

     /**
     * Retourne la liste des taches de l'utilisateur connecté
     */
    public function index()
    {
        
        $lestaches = $this->taskRepository->get();
        return response()->json(['data'=>$lestaches]);
    }

    /**
     * Enregistre une tache dans la section de l'utilisateur connecté
     */
    public function create(Request $request){
    
        try {
            
            $request->validate([
                'nom' => 'required',
                'prenom' => 'required',
                'tel' => 'required',
                'mail' => 'required|email|unique:contacts,mail,NULL,id,user_id,' . auth()->id(),
                'residence' => 'required',
                'categorie' => 'required|in:1,2,3', // Ajout de la validation pour la catégorie
                
            ]);


            $request->merge(['user_id' => auth()->id()]);

            $taskCreated = $this->taskRepository->store($request->all());
            return response()->json(['data'=>$taskCreated]);
        }catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], );
        }
    }

    //Afficher la liste des contacts dans chaque catégorie

    public function contactsByCategory($category) {
        $user = Auth::user();
    
        // Récupérer les contacts pour cet utilisateur dans la catégorie spécifiée
        $contacts = Contact::where('user_id', $user->id)->where('categorie', $category)->get();
    
        return response()->json(["data" => $contacts], 200);
    }
    

    
    

    public function update(Request $request, string $id)
    {
        // Vérifier si le contact existe
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(["message" => "Contact introuvable"], 404);
        }

        // Vérifier si l'utilisateur connecté est le propriétaire du contact
        if ($contact->user_id !== Auth::id()) {
            return response()->json(["message" => "Vous n'êtes pas autorisé à modifier ce contact"], 403);
        }

        // Vérifier que tous les champs requis sont fournis
        if (!$request->has('nom') || !$request->has('prenom') || !$request->has('tel') || !$request->has('mail') || !$request->has('residence') || !$request->has('categorie')) {
            return response()->json(["message" => "Tous les champs sont requis"], 400);
        }

        // Mettre à jour le contact
        $contact->nom = $request->nom;
        $contact->prenom = $request->prenom;
        $contact->tel = $request->tel;
        $contact->mail = $request->mail;
        $contact->residence = $request->residence;
        $contact->categorie = $request->categorie;
        $contact->save();

        return response()->json(["message" => "Mise à jour effectuée avec succès"], 200);
    }

    /**
     * Supprime une tache
     */
    public function destroy($id)
    {
        $taskDestroyed = $this->taskRepository->destroy($id);
        return response()->json(['data'=>$taskDestroyed]);
    }

    //pour rechercher un contact par son nom et afficher les données

    public function getContact($nom)
    {
        // Vérifier si un contact avec ce nom existe
        $user = Auth::user();

        // Rechercher tous les contacts pour cet utilisateur spécifique avec le nom donné
        $contacts = Contact::where('user_id', $user->id)->where('nom', $nom)->get();

        if($contacts->count() > 0)
        {
            return response()->json(["message" => "Contacts trouvés", "data" => $contacts], 200);
        }
        else 
        {
            return response()->json(["message" => "Aucun contact trouvé avec ce nom"], 404);
        }
    }



    // Trier la liste

   


    public function trierPar(Request $request)
    {
        
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            return response()->json(["message" => "Vous devez être connecté pour effectuer cette action"], 401);
        }

        // Récupérer l'utilisateur connecté
        $user = Auth::user();

        // Récupérer le paramètre de tri depuis les paramètres de requête
        $triPar = $request->query('tri_par');

        // Valider le paramètre de tri
        $validTriPar = in_array($triPar, ['nom', 'date_creation']);

        if (!$validTriPar) {
            return response()->json(["message" => "Le paramètre de tri doit être 'nom' ou 'date_creation'"], 400);
        }

        // Effectuer le tri en fonction du paramètre fourni
        if ($triPar === 'nom') {
            $contacts = $user->contacts()->orderBy('nom')->get();
        } elseif ($triPar === 'date_creation') {
            $contacts = $user->contacts()->orderBy('created_at')->get();
        }

        // Retourner la liste triée des contacts
        return response()->json(["message" => "Liste des contacts triée par $triPar", "data" => $contacts], 200);
    }

    //Récupérer la liste des contacts dans un fichier pdf

    public function genererPDF()
    {
        // Récupérer la liste des contacts
        $lestaches = $this->taskRepository->get();

        // Créer une instance de Dompdf avec des options
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        // Générer le contenu HTML du PDF
        $html = '<h1>Liste des contacts</h1>';
        $html .= '<table border="1" cellpadding="5">';
        $html .= '<tr><th>Nom</th><th>Prénom</th><th>Numéro</th><th>Email</th><th>Résidence</th><th>Catégorie</th></tr>';
        foreach ($lestaches as $lestache) {
            $html .= "<tr><td>{$lestache->nom}</td><td>{$lestache->prenom}</td><td>{$lestache->tel}</td><td>{$lestache->mail}</td><td>{$lestache->residence}</td><td>{$lestache->categorie}</td></tr>";
        }
        $html .= '</table>';

        // Charger le contenu HTML dans Dompdf
        $dompdf->loadHtml($html);

        // Rendre le PDF
        $dompdf->render();

        // Récupérer le contenu du PDF sous forme de chaîne
        $pdfContent = $dompdf->output();

        // Générer le nom du fichier PDF
        $fileName = 'liste_contacts_' . time() . '.pdf';

        // Retourner une réponse de téléchargement avec le contenu du PDF
        return response($pdfContent)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');

       
    }

}
<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Famille;
use App\Form\ProduitType;
use App\Repository\FamilleRepository;
use App\Repository\ProduitRepository;
use App\Repository\StockRepository;
use App\Repository\UniteDeMesureRepository;
use App\Repository\UvalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("produit_")
 */
class ProduitController extends AbstractController
{
    /**
     * @Route("nb", name="produit_nb")
     */
    public function nb(SessionInterface $session, Request $request)
    {
        if (!empty($request->request->get('nb_row'))) {
            $nb_of_row = $request->request->get('nb_row');
            $get_nb_row = $session->get('nb_row', []);
            if (!empty($get_nb_row)) {
                $session->set('nb_row', $nb_of_row);
            }
            $session->set('nb_row', $nb_of_row);
            //dd($session);
        }
        return $this->redirectToRoute('produit_new');
    }

    /**
     * @Route("sessionFamille", name="produit_sessionFamille")
     */
    public function sessionFamille(SessionInterface $session, Request $request)
    {
        if (!empty($request->request->get('famille'))) {
            $famille = $request->request->get('famille');
            $get_famille = $session->get('famille', []);
            if (!empty($get_famille)) {
                $session->set('famille', $famille);
            }
            $session->set('famille', $famille);
            // dd($session);
        }
        return $this->redirectToRoute('produit_new');
    }

    /**
     * Insertion et affichage des filieres
     * @Route("new", name="produit_new")
     */
    public function produit(SessionInterface $session,FamilleRepository $familleRepository,ProduitRepository $produitRepository, ManagerRegistry $end)
    {
        $sessionFamille=$session->get('famille',[]);
        //on cherche l'utilisateur connect??
        $user = $this->getUser();
        // if (!$user) {
        //     return $this->redirectToRoute('app_login');
        // }
       
        if (!empty($session->get('nb_row', []))) {
            $sessionLigne = $session->get('nb_row', []);
        }
        else{
            $sessionLigne = 1;
        }
        $sessionNb = $sessionLigne;
        $nb_row = array(1);

        if (!empty( $sessionNb)) {
           
            for ($i = 0; $i < $sessionNb; $i++) {
                $nb_row[$i] = $i;
            }
        }
       
        //on cree la methode qui permettra d'enregistrer les infos du post dans la bd
        function insert_into_db($data,$idFamille,FamilleRepository $familleRepository, ManagerRegistry $end)
        {
            foreach ($data as $key => $value) {
                $k[] = $key;
                $v[] = $value;
            }
            $k = implode(",", $k);
            $v = implode(",", $v);
            
            $famille=$familleRepository->find($idFamille);
            $produit = new Produit();
            $produit->setFamille($famille);
            $produit->setNom(ucfirst($data['produit']));
            $produit->setAlerte($data['alerte']);
            $produit->setCode(strtoupper($data['code']));
            $manager = $end->getManager();
            $manager->persist($produit);
            $manager->flush();
        }

        //si on clic sur le boutton enregistrer et que les champs du post ne sont pas vide
        if (isset($_POST['enregistrer'])) {
            
            //dd($session_nb_row);
            for ($i = 0; $i < $sessionNb; $i++) {
                $ref=rand(001,5599);
                $data = array(
                    'produit' => $_POST['produit' . $i],
                    'code'    => 'code_'.$ref,
                    'alerte' => $_POST['alerte'. $i]
                );
               
                insert_into_db($data,$sessionFamille,$familleRepository ,$end);
            }

            // return $this->redirectToRoute('niveaux_index');
        }

        return $this->render('produit/new.html.twig', [
            'nb_rows' => $nb_row,
            'familles'=>$familleRepository->findAll(),
            'produits'=>$produitRepository->findAll()
        ]);
    }

    /**
     * @Route("listeP", name="produit_listeP")
     */
    public function produitsListe(ProduitRepository $produitRepository){

        return $this->render('produit/produits.html.twig',[
            'produits'=>$produitRepository->findAll()
        ]);
    }

    /**
     * @Route("_{id}", name="produit_show", methods={"GET"})
     */
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="produit_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="produit_delete", methods={"POST"})
     */
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
    }
}

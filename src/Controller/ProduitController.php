<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Famille;
use App\Form\ProduitType;
use App\Repository\FamilleRepository;
use App\Repository\ProduitRepository;
use App\Repository\StockRepository;
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
        return $this->redirectToRoute('famille_index');
    }

    /**
     * Insertion et affichage des filieres
     * @Route("index_{id}", name="produit_index")
     */
    public function produit(SessionInterface $session,Famille $famille,FamilleRepository $familleRepository,ProduitRepository $produitRepository, Request $request, ManagerRegistry $end)
    {
        //$getIdFamille=$session->get('famille',[]);
        //$repoFam=$familleRepository->find($getIdFamille);
        // if (empty($getIdFamille)) {
        //     $session->set('famille',$famille);
        // }
        //$session->set('famille',$famille);
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        // if (!$user) {
        //     return $this->redirectToRoute('app_login');
        // }
        //on recupere la valeur du nb_row stocker dans la session
        $sessionNb = $session->get('nb_row', []);
        //on cree un tableau qui permettra de generer plusieurs champs dans le post
        //en fonction de la valeur de nb_row
        $nb_row = array(1);
        //pour chaque valeur du compteur i, on ajoutera un champs de plus en consirerant que 
        //nb_row par defaut=1
        if (!empty( $sessionNb)) {
           
            for ($i = 0; $i < $sessionNb; $i++) {
                $nb_row[$i] = $i;
            }
        }
        $session_nb_row=1;
        //on cree la methode qui permettra d'enregistrer les infos du post dans la bd
        function insert_into_db($data,Famille $famille,FamilleRepository $familleRepository, ManagerRegistry $end,$user)
        {
            foreach ($data as $key => $value) {
                $k[] = $key;
                $v[] = $value;
            }
            $k = implode(",", $k);
            $v = implode(",", $v);
            //echo $data['filiere'];
            $getFamille=$familleRepository->find($famille);
            $produit = new Produit();
            $produit->setFamille($famille);
            $produit->setNom(ucfirst($data['produit']));
            $produit->setRef(strtoupper($data['ref']));
            $produit->setCreatedAt(new \datetime);
            $manager = $end->getManager();
            $manager->persist($produit);
            $manager->flush();
        }

        //si on clic sur le boutton enregistrer et que les champs du post ne sont pas vide
        if (isset($_POST['enregistrer'])) {
            $session_nb_row = $session->get('nb_row', []);
            //dd($session_nb_row);
            for ($i = 0; $i < $session_nb_row; $i++) {
                $ref=rand(001,5599);
                $data = array(
                    'produit' => $_POST['produit' . $i],
                    'ref'    => 'ref_'.$ref
                );
               
                insert_into_db($data,$famille,$familleRepository ,$end,$user);
            }

            // return $this->redirectToRoute('niveaux_index');
        }

        return $this->render('produit/index.html.twig', [
            'nb_rows' => $nb_row,
            'famille'=>$famille,
            'produits'=>$produitRepository->findAll(),
            // 'famillesNb' => $familleRepository->count([
            //     'user' => $user
            // ]),
        ]);
    }

    /**
     * @Route("listeP", name="produit_listeP")
     */
    public function produitsListe(StockRepository $stockRepository){

        return $this->render('produit/produits.html.twig',[
            'stocks'=>$stockRepository->ListeStocksSelonFifo()
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

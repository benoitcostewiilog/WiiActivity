<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Activite;
use App\Entity\Site;
use App\Entity\Projet;
use App\Entity\Utilisateur;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ActiviteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;


class IndexController extends AbstractController
{
    /**
     * @Route("/test", name="test")
     */
    public function test()
    {
        $em = $this->getDoctrine()->getManager();

        for ($i=0; $i < 2000 ; $i++) { 
            $activite = new Activite;
            $activite->setdate(new \DateTime('2011-01-01T15:03:01.012345Z'));
            $activite->setTemps(3);
            dump("helle");
            dump($this->getDoctrine()->getRepository(Site::class)->findOneBy(["id"=>1]));
            $activite->setSite($this->getDoctrine()->getRepository(Site::class)->findOneBy(["id"=>1]));
            $activite->setProjet($this->getDoctrine()->getRepository(Projet::class)->findOneBy(["id"=>3]));
            $activite->setUtilisateur($this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(["id"=>1]));
            $activite->setTache('tache');
            
            $em->persist($activite);
            $em->flush();

        }
            return $this->redirectToRoute("index");
    }

    private function getAvancement($activite){
         $estime = $activite->getProjet()->getChargeEstime();
         $query = $this->getDoctrine()->getRepository(Activite::class)->findBy(
            ['projet' => $activite->getProjet()]
        );
        $total = 0;
        foreach($query as $q){
            $total += $q->getTemps();
        }
        return $total * 100 / $estime;
    }

    /**
     * @Route("/index", name="index")
     */
    public function index(Request $request, ActiviteRepository $activiteRepository)
    {
        $activiteRepository->findByFilter('',[],[],'','',[],'');
        if ($request->isXmlHttpRequest()) {
            $utilisateur = $request->request->get('utilisateur');
            $site = $request->request->get('site');
            $projet = $request->request->get('projet');
           
            $datedebut = $request->request->get('datedebut');
            $datefin = $request->request->get('datefin');


            $current = $request->request->get('current');
            $rowCount = $request->request->get('rowCount');
            $searchPhrase = $request->request->get('searchPhrase');
            $sort = $request->request->get('sort');

            $datedebut = \DateTime::createFromFormat("d/m/Y H:i:s", $datedebut . " 00:00:00");
            $datefin = \DateTime::createFromFormat("d/m/Y H:i:s", $datefin . " 23:59:59");

            $activites = $activiteRepository->findByFilter($utilisateur, $site, $projet, $datedebut, $datefin, $sort, $searchPhrase);
        
            if ($searchPhrase != "" ) {
                $count = count($activites->getQuery()->getResult());
            } else {
                $count = $activiteRepository->compte();
            }


            if ($rowCount != -1) {
                $min = ($current - 1) * $rowCount;
                $max = $rowCount;

                $activites->setMaxResults($max)
                    ->setFirstResult($min);
            }
            $activites = $activites->getQuery()->getResult();

            $rows = array();
            foreach ($activites as $activite) {
                $row = [
                    "id" => $activite->getId(),
                    "site" => $activite->getSite()->getName(),
                    "date" => $activite->getDate()->format('d-m-Y'),
                    "projet" => $activite->getProjet()->getName(),
                    "temps" => $activite->getTemps(),
                    "tache" => $activite->getTache(),
                    "avancement" => 1,//round($this->getAvancement($activite), 1)." %",
                    "progression" => $activite->getProjet()->getProgression(),
                ];
                array_push($rows, $row);
            }

            $data = array(
                "current" => intval($current),
                "rowCount" => intval($rowCount),
                "rows" => $rows,
                "total" => intval($count)
            );

            dump($data);
            return new JsonResponse($data);
        }

        $sites = $this->getDoctrine()->getRepository(Site::class)->findAll();
        $projets = $this->getDoctrine()->getRepository(Projet::class)->findAll();
        $utilisateurs = $this->getDoctrine()->getRepository(Utilisateur::class)->findAll();


        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'sites' => $sites,
            'projets' => $projets,
            'utilisateurs' => $utilisateurs,
            

        ]);
    }
}

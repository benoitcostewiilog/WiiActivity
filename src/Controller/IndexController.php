<?php

namespace App\Controller;

use App\Entity\Activite;
use App\Entity\Site;
use App\Entity\Projet;
use App\Entity\Utilisateur;
use App\Form\ActiviteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ActiviteRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Session\Session;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;


class IndexController extends AbstractController
{
    /**
     * @Route("/test", name="test")
     */
    public function test()
    {
        $em = $this->getDoctrine()->getManager();

        for ($i = 0; $i < 200; $i++) {
            $activite = new Activite;
            $activite->setdate(new \DateTime('2018-01-01T15:03:01.012345Z'));
            $activite->setTemps(3);

            $activite->setSite($this->getDoctrine()->getRepository(Site::class)->findOneBy(["id" => 1]));
            $activite->setProjet($this->getDoctrine()->getRepository(Projet::class)->findOneBy(["id" => 3]));
            $activite->setUtilisateur($this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(["id" => 1]));
            $activite->setTache('Tache ' . i);

            $em->persist($activite);
            $em->flush();

        }
        return $this->redirectToRoute("index");
    }

    private function getAvancement($activite)
    {
        $estime = $activite->getProjet()->getChargeEstime();
        $query = $this->getDoctrine()->getRepository(Activite::class)->findBy(
            ['projet' => $activite->getProjet()]
        );
        $total = 0;
        foreach ($query as $q) {
            $total += $q->getTemps();
        }
        return $total * 100 / $estime;
    }

    /**
     * @Route("/activite", name="index")
     */
    public function index(Request $request, ActiviteRepository $activiteRepository)
    {
        $activite = new Activite();
        $form = $this->createForm(ActiviteType::class, $activite);
        $session = $request->getSession();

        if ($request->isXmlHttpRequest()) {

            if (!$request->request->get('start')) {
                $utilisateur = $session->get('utilisateur');
                $site = $session->get('site');
                $projet = $session->get('projet');
                $datedebut = $session->get('datedebut');
                $datefin = $session->get('datefin');
            } else {
                $utilisateur = $request->request->get('utilisateur');
                $site = $request->request->get('site');
                $projet = $request->request->get('projet');
                $datedebut = $request->request->get('datedebut');
                $datefin = $request->request->get('datefin');
                $session->set('utilisateur', $request->request->get('utilisateur'));
                $session->set('site', $request->request->get('site'));
                $session->set('projet', $request->request->get('projet'));
                $session->set('datedebut', $request->request->get('datedebut'));
                $session->set('datefin', $request->request->get('datefin'));
            }

            $current = $request->request->get('current');
            $rowCount = $request->request->get('rowCount');
            $searchPhrase = $request->request->get('searchPhrase');
            $sort = $request->request->get('sort');

            $datedebut = \DateTime::createFromFormat("d/m/Y H:i:s", $datedebut . " 00:00:00");
            $datefin = \DateTime::createFromFormat("d/m/Y H:i:s", $datefin . " 23:59:59");

            $activites = $activiteRepository->findByFilter($utilisateur, $site, $projet, $datedebut, $datefin, $sort, $searchPhrase);

            if ($searchPhrase != "") {
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
                    "utilisateur" => $activite->getUtilisateur()->getNom(),
                    "site" => $activite->getSite()->getName(),
                    "date" => $activite->getDate()->format('d-m-Y'),
                    "projet" => $activite->getProjet()->getName(),
                    "temps" => $activite->getTemps(),
                    "tache" => $activite->getTache(),
                ];
                array_push($rows, $row);
            }

            $data = array(
                "current" => intval($current),
                "rowCount" => intval($rowCount),
                "rows" => $rows,
                "total" => intval($count)
            );

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
            'form' => $form->createView(),
            "f_utilisateur" => $statut = $session->get('utilisateur'),
            "f_site" => $statut = $session->get('site'),
            "f_projet" => $statut = $session->get('projet'),
            "f_datedebut" => $statut = $session->get('datedebut'),
            "f_datefin" => $statut = $session->get('datefin'),
        ]);
    }

    /**
     * @Route("/export", name="export")
     */
    public function export(ActiviteRepository $activiteRepository)
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizer], [new CsvEncoder(';')]);

        $callback = function ($dateTime) {
            return $dateTime instanceof \DateTime
                ? $dateTime->format('d/m/y')
                : '';
        };
        $normalizer->setCallbacks(array(
            'date' => $callback,
        ));

        $org = $activiteRepository->findAll();
        $data = $serializer->serialize($org, 'csv', array('groups' => array('activite')));
        $data = str_replace(
            "date;temps;tache;utilisateur.nom;projet.name;projet.progression;projet.charge_estime;site.name",
            "Date;Temps;Tache;Nom de l'utilisateur;Projet;Progression du projet;Charge estimée du projet;Site",
            $data
        );
        $fileName = "export_activites_" . date("d_m_Y") . ".csv";
        $response = new Response($data);
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8; application/excel');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $fileName);
        echo "\xEF\xBB\xBF"; // UTF-8 with BOM
        return $response;
    }

    private function isAlreadySameTache($array, $tache)
    {
        foreach ($array as $a) {
            if (strcmp($a['tache'], $tache) == 0) {
                return (1);
            }
        }
        return (0);
    }

    /**
     * @Route("/tache", name="get_tache")
     */
    public function getTache(Request $request, ActiviteRepository $activiteRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $q = $request->query->get('q');
            $activites = $activiteRepository->findBySearch($q);
            $rows = array();
            foreach ($activites as $activite) {
                $row = [
                    "id" => $activite->getId(),
                    "tache" => $activite->getTache(),
                ];
                if ($this->isAlreadySameTache($rows, $activite->getTache()) == 0) {
                    array_push($rows, $row);
                }
            }

            $data = array(
                "total_count" => count($rows),
                "items" => $rows,
            );
            return new JsonResponse($data);
        }
        throw new NotFoundHttpException('404 not found');
    }
}

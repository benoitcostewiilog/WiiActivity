<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @Route("/utilisateur")
 */
class UtilisateurController extends AbstractController
{

    /**
     * @Route("/", name="utilisateur_index", methods="GET")
     */
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('utilisateur/index.html.twig', ['utilisateurs' => $utilisateurRepository->findAll()]);
    }

    /**
    * @Route ("/admin", name="admin")
    */
    public function adminUtilisateur(UtilisateurRepository $utilisateurRepository,  UserPasswordEncoderInterface $passwordEncoder, Request $request): Response
    {

        if ($request->isXmlHttpRequest()) {

            $current = $request->request->get('current');
            $rowCount = $request->request->get('rowCount');
            $searchPhrase = $request->request->get('searchPhrase');
            $sort = $request->request->get('sort');

            $utilisateurs = $utilisateursRepository->findBySearchSort($searchPhrase, $sort);

            if ($searchPhrase != "") {
                $count = count($utilisateurs->getQuery()->getResult());
            } else {
                $count = count($utilisateursRepository->findAll());
            }

            if ($rowCount != -1) {
                $min = ($current - 1) * $rowCount;
                $max = $rowCount;

                $utilisateurs->setMaxResults($max)
                    ->setFirstResult($min);
            }

            $utilisateurs = $utilisateurs->getQuery()->getResult();

            $rows = array();
            foreach ($utilisateurs as $utilisateur) {
                $roles = $utilisateur->getRoles();
                $roles_string = "";
                foreach ($roles as $role) {
                    $roles_string = $role . ", " . $roles_string;
                }

                // enlève les deux derniers caractères
                $roles_string = substr($roles_string, 0, -2);


                // format de la derniere date de connexion
                if ($utilisateur->getLastLogin()) {
                    $lastLogin = date_diff(new \Datetime(), $utilisateur->getLastLogin());

                    $format = "Il y a ";
                    if ($lastLogin->y) {
                        $format = $format . "environ " . $lastLogin->y . "an(s) " . $lastLogin->m . "mois";
                    } else if ($lastLogin->m) {
                        $format = $format . "environ " . $lastLogin->m . "mois " . $lastLogin->d . "jour(s)";
                    } else if ($lastLogin->d) {
                        $format = $format . $lastLogin->d . "jour(s) " . $lastLogin->h . "heure(s)";
                    } else if ($lastLogin->h) {
                        $format = $format . $lastLogin->h . "h" . $lastLogin->i . "min";
                    } else {
                        $format = $format . $lastLogin->i . "min";
                    }

                    $lastLogin = $lastLogin->format($format);

                } else {
                    $lastLogin = "Aucune connexion";
                }


                $row = [
                    "id" => $utilisateur->getId(),
                    "username" => $utilisateur->getUsername(),
                    "email" => $utilisateur->getEmail(),
                    "groupe" => $utilisateur->getGroupe(),
                    "lastLogin" => $lastLogin,
                    "roles" => $roles_string,
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

        $user = new Utilisateur();
        $form_creation = $this->createForm(UtilisateurType::class, $user);

        $form_modif = $this->createFormBuilder()
        ->add('id_user', HiddenType::class, array(
            'mapped' => false,
        ))
        ->add('email', EmailType::class, array(
            'label' => "Adresse email"
        ))
        ->add('username', TextType::class, array(
            'label' => "Nom d'utilisateur"
        ))
        ->add('plainPassword', PasswordType::class, array(
            'label' => "Réinitialiser Mot de Passe",
        ))
        ->add('roles', ChoiceType::class, array(
            'label' => 'Rôles',
            'choices' => array(
                'Utilisateur' => 'ROLE_USER',
                'Utilisateur parc' => 'ROLE_PARC',
                'Admin parc' => 'ROLE_PARC_ADMIN',
            ),
            'multiple' => true,
        ))
        ->getForm();

        return $this->render("security/utilisateurs.html.twig", array(
            'utilisateurs' => $utilisateurRepository->findAll(),
            'form_creation' => $form_creation->createView(),
            'form_modif' => $form_modif->createView()

        ));
    }


    /**
     * @Route("/new", name="utilisateur_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($utilisateur);
            $em->flush();

            return $this->redirectToRoute('utilisateur_index');
        }

        return $this->render('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="utilisateur_show", methods="GET")
     */
    public function show(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/show.html.twig', ['utilisateur' => $utilisateur]);
    }

    /**
     * @Route("/{id}/edit", name="utilisateur_edit", methods="GET|POST")
     */
    public function edit(Request $request, Utilisateur $utilisateur): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('utilisateur_index', ['id' => $utilisateur->getId()]);
        }

        return $this->render('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="utilisateur_delete", methods="DELETE")
     */
    public function delete(Request $request, Utilisateur $utilisateur): Response
    {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($utilisateur);
            $em->flush();
        }

        return $this->redirectToRoute('utilisateur_index');
    }

   
}

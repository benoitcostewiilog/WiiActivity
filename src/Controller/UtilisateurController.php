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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Doctrine\Common\Annotations\AnnotationReader;

/**
 * @Route("/utilisateur")
 */
class UtilisateurController extends AbstractController
{

    /**
     * @Route("/", name="utilisateur_index", methods="GET")
     */
    public function index(UtilisateurRepository $utilisateurRepository) : Response
    {
        return $this->render('security/utilisateurs.html.twig', ['utilisateur' => $utilisateurRepository->findAll()]);
    }

    /**
     * @Route ("/admin", name="utilisateur_admin")
     */
    public function adminUtilisateur(UtilisateurRepository $utilisateurRepository, UserPasswordEncoderInterface $passwordEncoder, Request $request) : Response
    {

        if ($request->isXmlHttpRequest()) {

            $current = $request->request->get('current');
            $rowCount = $request->request->get('rowCount');
            $searchPhrase = $request->request->get('searchPhrase');
            $sort = $request->request->get('sort');

            $utilisateur = $utilisateurRepository->findBySearchSort($searchPhrase, $sort);

            if ($searchPhrase != "") {
                $count = count($utilisateur->getQuery()->getResult());
            } else {
                $count = count($utilisateurRepository->findAll());
            }

            if ($rowCount != -1) {
                $min = ($current - 1) * $rowCount;
                $max = $rowCount;

                $utilisateur->setMaxResults($max)
                    ->setFirstResult($min);
            }

            $utilisateur = $utilisateur->getQuery()->getResult();

            $rows = array();
            foreach ($utilisateur as $utilisateur) {
                $roles = $utilisateur->getRoles();
                $roles_string = "";
                foreach ($roles as $role) {
                    $roles_string = $role . ", " . $roles_string;
                }

                // enlève les deux derniers caractères
                $roles_string = substr($roles_string, 0, -2);

                $row = [
                    "id" => $utilisateur->getId(),
                    "username" => $utilisateur->getUsername(),
                    "nom" => $utilisateur->getNom(),
                    "prenom" => $utilisateur->getPrenom(),
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
            ->add('username', TextType::class, array(
                'label' => "Nom d'utilisateur"
            ))
            ->add('plainPassword', PasswordType::class, array(
                'label' => "Réinitialiser le Mot de Passe",
            ))
            ->add('roles', ChoiceType::class, array(
                'label' => 'Rôles',
                'choices' => array(
                    'Utilisateur' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ),
                'multiple' => true,
            ))
            ->getForm();

        return $this->render("security/utilisateurs.html.twig", array(
            'utilisateur' => $utilisateurRepository->findAll(),
            'form_creation' => $form_creation->createView(),
            'form_modif' => $form_modif->createView()

        ));
    }

    /**
     * @Route("/new", name="utilisateur_new", methods="GET|POST")
     */
    public function new(Request $request) : Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($utilisateur);
            $em->flush();

            return $this->redirectToRoute('utilisateur_admin');
        }

        return $this->render('utilisateurs/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/create", name="utilisateur_index_create", methods="GET|POST")
     */
    public function create(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {

        if ($request->isXmlHttpRequest()) {

            $new_user = new Utilisateur();

            $user = $request->request->get('user');

            $new_user->setUsername($user[0]["value"]);
            $new_user->setNom($user[1]["value"]);
            $new_user->setPrenom($user[2]["value"]);
            $password = $passwordEncoder->encodePassword($new_user, $user[3]["value"]);
            $new_user->setPassword($password);

            $new_user->setRoles(array('ROLE_USER'));

            $em->persist($new_user);
            $em->flush();
            $session = $request->getSession();
            $session->getFlashBag()->add('success', 'Félicitations ! L\'utilisateur a été créé avec succès !');
    
            return new JsonResponse(true);
        }
        throw new NotFoundHttpException('404 Léo not found');
    }

    /**
     * @Route("/modif", name="utilisateur_index_modif", methods="GET|POST")
     */
    public function modif(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {

        if ($request->isXmlHttpRequest()) {

            $id = $request->request->get('id');
            $user = $em->getRepository(Utilisateur::class)->find($id);

            $encoders = array(new JsonEncoder());
            $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
            $normalizer = new ObjectNormalizer($classMetadataFactory);

            $serializer = new Serializer([$normalizer], $encoders);
            $jsonContent = $serializer->serialize($user, 'json', array('groups' => array('user')));
            return new JsonResponse($jsonContent);
        }
        throw new NotFoundHttpException('404 Léo not found');
    }

    /**
     * @Route("/modif_bis", name="utilisateur_index_modif_bis", methods="GET|POST")
     */
    public function modif_bis(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($request->isXmlHttpRequest()) {
            $user_modif = $request->request->get('user');
            $id = $user_modif[1]["value"];

            $user = $em->getRepository(Utilisateur::class)->find($id);
            $user->setUsername($user_modif[0]["value"]);
            $plain_password = $user_modif[2]["value"];
            if ($plain_password) {
                $new_password = $passwordEncoder->encodePassword($user, $plain_password);
                $user->setPassword($new_password);
            }

            $roles = array();
            for ($i = 3; $i < count($user_modif) - 1; ++$i) {
                array_push($roles, $user_modif[$i]["value"]);
            }
            $user->setRoles($roles);

            $em->flush();
            $session = $request->getSession();
            $session->getFlashBag()->add('success', 'Félicitations ! L\'utilisateur a été modifié avec succès !');

            return new JsonResponse();
        }
        throw new NotFoundHttpException('404 Léo not found');
    }

    /**
     * @Route("/ajax/username", name="utilisateur_username_error", methods="GET|POST")
     */
    public function utlisateur_username_error(Request $request) : Response
    {
        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();
            $username = $request->request->get('username');

            $utilisateurs = $em->getRepository(Utilisateur::class)->findAll();
            foreach ($utilisateurs as $utilisateur) {
                if (!strcmp($username, $utilisateur->getUsername())
                    && $utilisateur->getUsername() != null) {
                    return new JsonResponse(true);
                }
            }
            return new JsonResponse(false);
        }
        throw new NotFoundHttpException('404 Léo not found');
    }

    /**
     * @Route("/{id}", name="utilisateur_show", methods="GET")
     */
    public function show(Utilisateur $utilisateur) : Response
    {
        return $this->render('utilisateurs/show.html.twig', ['utilisateur' => $utilisateur]);
    }

    /**
     * @Route("/{id}/edit", name="utilisateur_edit", methods="GET|POST")
     */
    public function edit(Request $request, Utilisateur $utilisateur) : Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('utilisateur_index', ['id' => $utilisateur->getId()]);
        }

        return $this->render('utilisateurs/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="utilisateur_delete", methods="DELETE")
     */
    public function delete(Request $request, Utilisateur $utilisateur) : Response
    {
        if ($this->isCsrfTokenValid('delete' . $utilisateur->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($utilisateur);
            $em->flush();
        }

        return $this->redirectToRoute('utilisateur_index');
    }

    /**
     * @Route("/{id}/remove", name="utilisateur_remove", methods="DELETE")
     */
    public function remove(Request $request, Utilisateur $utilisateur) : Response
    {
        dump("aie");

        $em = $this->getDoctrine()->getManager();
        $em->remove($utilisateur);
        $em->flush();
        $session = $request->getSession();
        $session->getFlashBag()->add('success', 'Félicitations ! L\'utilisateur a été supprimé avec succès !');


        return $this->redirectToRoute('utilisateur_admin');
    }

}

<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\MemberType;
use App\Form\PrenomType;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/member")
 */
class MemberController extends AbstractController
{
    /**
     * @Route("/", name="member_index", methods={"GET"})
     * @param MemberRepository $memberRepository
     * @return Response
     */
    public function index(MemberRepository $memberRepository): Response
    {
        return $this->render('member/index.html.twig', [
            'members' => $memberRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="member_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member,[
            'validation_groups' => 'registration'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($member);
            $entityManager->flush();

            return $this->redirectToRoute('member_index');
        }

        return $this->render('member/new.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="member_show", methods={"GET"})
     * @param Member $member
     * @return Response
     */
    public function show(Member $member): Response
    {
        return $this->render('member/show.html.twig', [
            'member' => $member,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="member_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Member $member
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function edit(Request $request, Member $member, UserPasswordEncoderInterface $encoder): Response
    {
        $form = $this->createForm(MemberType::class, $member,[
            'validation_groups' => ['Default']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            if($password = $form['password']->getData()){
                $member->setPassword($encoder->encodePassword($member,$password));
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('member_index', [
                'id' => $member->getId(),
            ]);
        }

        return $this->render('member/edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="member_delete", methods={"DELETE"})
     * @param Request $request
     * @param Member $member
     * @return Response
     */
    public function delete(Request $request, Member $member): Response
    {
        if ($this->isCsrfTokenValid('delete'.$member->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($member);
            $entityManager->flush();
        }

        return $this->redirectToRoute('member_index');
    }
    /**
     * @return Response
     * @Route("/connected/success",name="member.connected.success")
     */
    public function memberConnected(): Response
    {
        return $this->render('member/show.html.twig', [
            'member' => $this->getUser()
        ]);
    }

    /**
     * @return Response
     * @Route("/change/prenom/", name="member.change.prenom")
     */
    public function changePrenom(Request $request): Response
    {
        $member = $this->getUser();
        $form = $this->createForm(PrenomType::class, $member,[
           // 'validation_groups' => ['Default']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
/*
            if($password = $form['password']->getData()){
                $member->setPassword($encoder->encodePassword($member,$password));
            }
*/
            //$this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('member_index', [
                'id' => $member->getId(),
            ]);
        }

        return $this->render('member/change_prenom.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Member;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname',TextType::class,[
                'attr' => [
                    'placeholder' => 'Ex: Jacques'
                ]
            ])
            ->add('lastname',TextType::class,[
                'attr' => [
                    'placeholder' => 'Ex: Clouseau'
                ]
            ])
            ->add('username',TextType::class,[
                'attr' => [
                    'placeholder' => 'Ex: pinkPanther'
                ]
            ])
            ->add('email',EmailType::class,[
                'invalid_message'=> 'votre email est incorrect',
                'attr' => [
                    'placeholder' => 'Ex: pinkPanther@mail.com'
                ]
            ])
            ->add('password',RepeatedType::class,
                [
                    'mapped' => false,
                    'type' => PasswordType::class,
                    'invalid_message' => 'The password fields must match.',
                    'options' => ['attr' => ['class' => 'password-field']],

                    'first_options'  => ['label'=>'Password','attr'=>['placeholder'=>'Mot de passe']],
                    'second_options' => ['label'=>'Confirm password','attr'=>['placeholder'=>'Confirmez votre mot de passe']],])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Member::class,
        ]);
    }
}

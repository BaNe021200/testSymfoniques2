<?php


namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username',TextType::class,[
                'attr' =>[
                    'placeholder' => 'entrez votre pseudo'
                ],
                'constraints' => [
                    new NotBlank(['message'=>'ce champ ne peut rester vide !'])
                ]
            ])
            ->add('password',PasswordType::class,[
                'attr' =>[
                    'placeholder' => 'entrez votre mot de passe'
                ],
                'constraints' => [
                    new NotBlank(['message'=>'votre mot de passe est malheureusement requis !'])
                ]

            ])
            ->setAction('/security/validate')

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null

        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_login';
    }

}
<?php


namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TotpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code',TextType::class,[
                'attr' =>[
                    'placeholder' => 'Code GoogleAuthenticator'
                ]
            ])

            ->add('totpNoMore',CheckboxType::class,[
                'label' =>' Je me connecte souvent à ce compte, ne plus me demander.',

                'required' => false,
                'attr'=>[
                    'data-toggle' => 'toggle',
                    'data-on' => 'oui',
                    'data-off' => 'non',
                    'data-onstyle' => 'success',
                    'data-offstyle' => 'danger',
                ]
            ])


            ->add('submit', SubmitType::class,[
                'attr' =>[
                    'class' =>'btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class' => null
        ]);
    }

}
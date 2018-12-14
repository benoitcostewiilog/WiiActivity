<?php

namespace App\Form;

use App\Entity\Activite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Projet;
use App\Entity\Site;

class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', DateType::class, array(
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'js-datepicker', 'placeholder' => 'Date', ]
            ))
            ->add('temps', NumberType::class, array(
                'attr' => array(
                    'placeholder' => 'Heure de travail',
                    'min' => 0,
                    'max' => 10000,
                    'step' => 0.25,
                ),
            ))
            ->add('site', EntityType::class, array(
                'class' => Site::class,
                'choice_label' => 'name',
                'label' => 'Site/Client',
                'placeholder' => '',
                'attr' => array(
                    'placeholder' => 'Site/Client',
                ),
            ))
            ->add('tache', TextType::class, array(
                'attr' => array(
                    'placeholder' => 'Tâche',
                ),
            ))
            ->add('utilisateur')
            ->add('projet', EntityType::class, array(
                'class' => Projet::class,
                'choice_label' => 'name',
                'label' => 'Projet',
                'placeholder' => '',
                'attr' => array(
                    'placeholder' => 'Projet',
                ),
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activite::class,
        ]);
    }
}

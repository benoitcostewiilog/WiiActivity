<?php

namespace App\Form;

use App\Entity\Activite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Projet;

class ActiviteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date',  DateType::class, array(
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/mm/yyyy',
                'attr' => ['class' => 'js-datepicker']))
            ->add('temps')
            ->add('site')
            ->add('tache')
            ->add('utilisateur')
            ->add('projet',  EntityType::class, array(
				'class' => Projet::class,
				'choice_label' => 'name',
				'label' => 'projet',
				'placeholder' => '',
			))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activite::class,
        ]);
    }
}

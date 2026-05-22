<?php

namespace App\Form;

use App\Entity\Nota;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo', TextType::class, ['label' => 'Título'])
            ->add('categoria', TextType::class, ['label' => 'Categoría'])
            ->add('contenido', TextareaType::class, ['label' => 'Contenido']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Nota::class]);
    }
}

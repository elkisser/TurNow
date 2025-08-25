<?php

namespace App\Form;

use App\Entity\Turno;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ReservaTurnoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('usuario_email', EmailType::class, [ // ← Cambiado de 'email' a 'usuario_email'
                'label' => 'Tu email',
                'attr' => [
                    'placeholder' => 'ejemplo@email.com',
                    'class' => 'form-control'
                ]
            ])
            ->add('fecha', DateType::class, [
                'label' => 'Fecha',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('hora', TimeType::class, [
                'label' => 'Hora',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('reservar', SubmitType::class, [
                'label' => 'Reservar Turno',
                'attr' => ['class' => 'btn btn-primary btn-lg w-100']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Turno::class,
        ]);
    }
}
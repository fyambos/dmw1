<?php
// src/Form/Type/TicketType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use App\Entity\Ticket;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class)
            ->add('status', TextType::class)
            ->add('summary', TextType::class)
            ->add('reporter', TextType::class)
            ->add('assignee', TextType::class)
            ->add('save', SubmitType::class)
        ;
    }

    //it's generally a good idea to explicitly specify the data_class option by adding the following to your form type class
    //note: need to import (use) Ticket and OptionsResolver
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
?>
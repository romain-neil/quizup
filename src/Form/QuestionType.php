<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class QuestionType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('libele', TextareaType::class, [
				'label' => 'Question : '
			])
			->add('image', FileType::class, [
				'label' => 'Image',
				'required' => false
			]);

		$alphabet = range('a', 'd');

		for($i = 1; $i < 5; $i++) {
			$builder
				->add('answer-lbl-' . $alphabet[$i - 1], TextType::class, [
					'label' => 'Réponse ' . $i . ' :',
					'mapped' => false
				])
				->add('answer-rd-' . $alphabet[$i - 1], CheckboxType::class, [
					'label' => 'Réponse correcte',
					'required' => false,
					'mapped' => false
				]);
		}
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'data_class' => Question::class,
		]);
	}

}

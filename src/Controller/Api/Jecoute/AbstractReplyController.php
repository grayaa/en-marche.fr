<?php

namespace App\Controller\Api\Jecoute;

use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\DataSurveyAwareInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractReplyController extends AbstractController
{
    protected $entityManager;
    protected $validator;
    protected $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    public function handleRequest(Request $request, DataSurveyAwareInterface $object): Response
    {
        if ($object->getDataSurvey()) {
            return $this->json([
                'code' => 'already_replied',
                'message' => 'La réponse a été déjà envoyée',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->serializer->deserialize($request->getContent(), DataSurvey::class, 'json', [
            AbstractObjectNormalizer::GROUPS => array_merge(['data_survey_write'], $this->getCustomDeserializeGroups()),
            AbstractObjectNormalizer::OBJECT_TO_POPULATE => $dataSurvey = $this->initializeDataSurvey($object),
        ]);

        $errors = $this->validator->validate($dataSurvey);

        if ($errors->count()) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($dataSurvey);

        $object->setDataSurvey($dataSurvey);
        $this->postHandleAction();

        $this->entityManager->flush();

        $this->dispatch();

        return $this->json($dataSurvey, Response::HTTP_CREATED, [], ['groups' => 'data_survey_read']);
    }

    protected function postHandleAction(): void
    {
    }

    protected function dispatch(): void
    {
    }

    protected function initializeDataSurvey(DataSurveyAwareInterface $object): DataSurvey
    {
        return new DataSurvey();
    }

    protected function getCustomDeserializeGroups(): array
    {
        return [];
    }
}

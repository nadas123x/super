<?php

namespace App\Listener;

use App\Entity\Property;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ImageCacheSubscriber implements EventSubscriber
{

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var UploaderHelper
     */
    protected $uploaderHelper;

    public function __construct(CacheManager $cacheManager, UploaderHelper $uploaderHelper)
    {
        $this->cacheManager = $cacheManager;
        $this->uploaderHelper = $uploaderHelper;
    }

    public function getSubscribedEvents()
    {
        // On retourne les évènements que l'on veut écouter
        return [
            'preRemove',
            'preUpdate'
        ];
    }

    // On écoute quand une entité est supprimée
    public function preRemove(LifecycleEventArgs $args)
    {
        // On récupère l'entité qui est modifiée
        $entity = $args->getEntity();

        // Si l'entitée n'est pas une instance de Property, on ne va pas plus loin
        if (!$entity instanceof Property) {
            return;
        }

        // Avec le cacheManager, on supprime imageFile
        $this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));
    }

    // On écoute quand une entité est modifiée
    public function preUpdate(PreUpdateEventArgs $args)
    {
        // On récupère l'entité qui est modifiée
        $entity = $args->getEntity();

        // Si l'entitée n'est pas une instance de Property, on ne va pas plus loin
        if (!$entity instanceof Property) {
            return;
        }

        // Si l'image de l'entité est une instance de UploadedFile (= si on a chargé une image, on supprime l'ancienne)
        if ($entity->getImageFile() instanceof UploadedFile) {
            // Avec le cacheManager, on supprime imageFile
            $this->cacheManager->remove($this->uploaderHelper->asset($entity, 'imageFile'));
        }
    }
}

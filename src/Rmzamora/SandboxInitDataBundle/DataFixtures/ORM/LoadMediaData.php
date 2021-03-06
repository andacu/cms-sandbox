<?php

/*
 * This file is part of the RmzamoraSandboxInitDataBundle package.
 *
 * (c) mell m. zamora <me@mellzamora.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rmzamora\SandboxInitDataBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\MediaInterface;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

class LoadMediaData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    private $container;

    function getOrder()
    {
        return 3;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager)
    {
        $gallery = $this->getGalleryManager()->create();

        $manager = $this->getMediaManager();
        $faker = $this->getFaker();

        $files = Finder::create()
            ->name('*.jpg')
            ->in(__DIR__.'/../data/files/slides');

        $i = 0;
        foreach ($files as $pos => $file) {
            $media = $manager->create();
            $media->setBinaryContent($file);
            $media->setEnabled(true);
            $media->setDescription($faker->sentence(15));

            $this->addReference('sonata-media-'.($i++), $media);

            $manager->save($media, 'default', 'sonata.media.provider.image');

            $this->addMedia($gallery, $media);
        }

//        $videos = array(
//            'ythUVU31Y18' => 'sonata.media.provider.youtube'
////            'xdw0tz'      => 'sonata.media.provider.dailymotion',
////            '9636197'     => 'sonata.media.provider.vimeo'
//        );
//
//        foreach ($videos as $video => $provider) {
//            $media = $manager->create();
//            $media->setBinaryContent($video);
//            $media->setEnabled(true);
//
//            $manager->save($media, 'default', $provider);
//
//            $this->addMedia($gallery, $media);
//        }

        $gallery->setEnabled(true);
        $gallery->setName($faker->sentence(4));
        $gallery->setDefaultFormat('small');
        $gallery->setContext('default');

        $this->getGalleryManager()->update($gallery);

        $this->addReference('media-gallery', $gallery);


        //add news data
        $i = 0;
        $files = Finder::create()
            ->name('*.*')
            ->in(__DIR__.'/../data/files/news');

        foreach ($files as $pos => $file) {
            $media = $manager->create();
            $media->setBinaryContent($file);
            $media->setEnabled(true);
            $media->setDescription($faker->sentence(15));

            $this->addReference('sonata-media-news-'.($i++), $media);

            $manager->save($media, 'news', 'sonata.media.provider.image');
        }

        //add logos
        $i = 0;
        $files = Finder::create()
            ->name('*.*')
            ->in(__DIR__.'/../data/files/logos');

        foreach ($files as $pos => $file) {
            $media = $manager->create();
            $media->setBinaryContent($file);
            $media->setEnabled(true);
            $media->setDescription($faker->sentence(15));

            $this->addReference('sonata-media-logos-'.($i++), $media);

            $manager->save($media, 'default', 'sonata.media.provider.image');
        }


    }

    /**
     * @param \Sonata\MediaBundle\Model\GalleryInterface $gallery
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function addMedia(GalleryInterface $gallery, MediaInterface $media)
    {
        $galleryHasMedia = new \Application\Sonata\MediaBundle\Entity\GalleryHasMedia();
        $galleryHasMedia->setMedia($media);
        $galleryHasMedia->setPosition(count($gallery->getGalleryHasMedias()) + 1);
        $galleryHasMedia->setEnabled(true);

        $gallery->addGalleryHasMedias($galleryHasMedia);
    }

    /**
     * @return \Sonata\MediaBundle\Model\MediaManagerInterface
     */
    public function getMediaManager()
    {
        return $this->container->get('sonata.media.manager.media');
    }

    /**
     * @return \Sonata\MediaBundle\Model\MediaManagerInterface
     */
    public function getGalleryManager()
    {
        return $this->container->get('sonata.media.manager.gallery');
    }

    /**
     * @return \Faker\Generator
     */
    public function getFaker()
    {
        return $this->container->get('faker.generator');
    }
}

<?php

namespace AppBundle\Service;

use AppBundle\Entity\Groups;
use Symfony\Component\Filesystem\Filesystem;

class ImageLoader
{
    protected $webPath;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @return string
     */
    public function getWebPath()
    {
        return $this->webPath;
    }

    public function __construct($rootDir, Filesystem $fs)
    {
        $this->webPath = $rootDir . '/../web';
        $this->fs = $fs;
    }

    /**
     * Копирует картинку из инстаграма на сервак
     * @param Groups $groups
     *
     * @return string
     */
    public function copy(Groups $groups)
    {
        $folder = $this->createFolder();
        $fileName = $this->createFileName($groups);

        $this->fs->copy($groups->getGroupAvatar(), $this->getWebPath() . '/' . $folder.$fileName);

        return $folder.$fileName;
    }

    private function createFolder()
    {
        $folderName = '/group_images/'.date('d_m_Y') . '/';
        $this->fs->mkdir($this->getWebPath() . $folderName);

        return $folderName;
    }

    private function createFileName(Groups $groups)
    {
        $ext    = pathinfo($groups->getGroupAvatar(), PATHINFO_EXTENSION);
        $name   = time().pathinfo($groups->getLink(), PATHINFO_FILENAME);

        return sprintf(
            "%s.%s",
            md5($name),
            $ext
        );
    }

    public function removeImage($filePath)
    {
        $pathToFile = $this->getWebPath() . '/' . $filePath;

        return $this->fs->remove($pathToFile);
    }
}
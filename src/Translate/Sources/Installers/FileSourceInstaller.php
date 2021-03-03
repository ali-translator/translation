<?php

namespace ALI\Translation\Translate\Sources\Installers;

/**
 * Class
 */
class FileSourceInstaller implements SourceInstallerInterface
{
    /**
     * @var string
     */
    protected $directoryPath;

    /**
     * @var string
     */
    protected $fileExtension;

    /**
     * @param string $directoryPath
     * @param string $fileExtension
     */
    public function __construct($directoryPath, $fileExtension)
    {
        $this->directoryPath = $directoryPath;
        $this->fileExtension = $fileExtension;
    }

    /**
     * @inheritDoc
     */
    public function isInstalled()
    {
        $iterator = $this->getFilesIterator();

        return $iterator->valid();
    }

    /**
     * @inheritDoc
     */
    public function install()
    {
        if (!file_exists($this->directoryPath)) {
            mkdir($this->directoryPath);
        }
    }

    /**
     * @inheritDoc
     */
    public function destroy()
    {
        $iterator = $this->getFilesIterator();
        while ($iterator->valid()) {
            unlink($iterator->current()->getPathname());
            $iterator->next();
        }
    }

    /**
     * @return \GlobIterator
     */
    protected function getFilesIterator()
    {
        return new \GlobIterator($this->directoryPath . DIRECTORY_SEPARATOR . '*.' . $this->fileExtension);
    }
}

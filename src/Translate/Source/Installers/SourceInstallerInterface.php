<?php

namespace ALI\Translation\Translate\Source\Installers;

/**
 * Interface
 */
interface SourceInstallerInterface
{
    /**
     * @return bool
     */
    public function isInstalled();

    /**
     * Install
     */
    public function install();

    /**
     * Destroy
     */
    public function destroy();
}

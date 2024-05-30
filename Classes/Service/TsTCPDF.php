<?php
namespace Extcode\TCPDF\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class TsTCPDF extends \TCPDF
{
    /**
     * @var array<mixed>
     */
    protected $pdfSettings;

    /**
     * @var string
     */
    protected $pdfType;

    public function getCartPdfType(): string
    {
        return $this->pdfType;
    }

    public function setCartPdfType(string $pdfType): void
    {
        $this->pdfType = $pdfType;
    }

    /**
     * @param array<mixed> $settings
     * @return void
     */
    public function setSettings(array $settings): void
    {
        $this->pdfSettings = $settings;
    }

    public function header(): void
    {
        if (!empty($this->pdfSettings[$this->pdfType])) {
            if (!empty($this->pdfSettings[$this->pdfType]['header'])) {
                if (!empty($this->pdfSettings[$this->pdfType]['fontSize'])) {
                    $this->SetFontSize($this->pdfSettings[$this->pdfType]['fontSize']);
                }

                if (is_array($this->pdfSettings[$this->pdfType]['header']['html'] ?? null)) {
                    foreach ($this->pdfSettings[$this->pdfType]['header']['html'] as $partName => $partConfig) {
                        $this->renderStandaloneView(
                            $this->pdfSettings[$this->pdfType]['header']['html'][$partName]['templatePath'],
                            $partName,
                            $partConfig
                        );
                    }
                }

                if (is_array($this->pdfSettings[$this->pdfType]['header']['line'] ?? null)) {
                    foreach ($this->pdfSettings[$this->pdfType]['header']['line'] as $partName => $partConfig) {
                        $this->Line(
                            $partConfig['x1'],
                            $partConfig['y1'],
                            $partConfig['x2'],
                            $partConfig['y2'],
                            $partConfig['style']
                        );
                    }
                }
            }
        }
    }

    public function footer(): void
    {
        if (!empty($this->pdfSettings[$this->pdfType])) {
            if (!empty($this->pdfSettings[$this->pdfType]['footer'])) {
                if (!empty($this->pdfSettings[$this->pdfType]['fontSize'])) {
                    $this->SetFontSize($this->pdfSettings[$this->pdfType]['fontSize']);
                }

                if (is_array($this->pdfSettings[$this->pdfType]['footer']['html'] ?? null)) {
                    foreach ($this->pdfSettings[$this->pdfType]['footer']['html'] as $partName => $partConfig) {
                        $this->renderStandaloneView(
                            $this->pdfSettings[$this->pdfType]['footer']['html'][$partName]['templatePath'],
                            $partName,
                            $partConfig
                        );
                    }
                }

                if (is_array($this->pdfSettings[$this->pdfType]['footer']['line'] ?? null)) {
                    foreach ($this->pdfSettings[$this->pdfType]['footer']['line'] as $partName => $partConfig) {
                        $this->Line(
                            $partConfig['x1'],
                            $partConfig['y1'],
                            $partConfig['x2'],
                            $partConfig['y2'],
                            $partConfig['style']
                        );
                    }
                }
            }
        }
    }

    /**
     * render Standalone View
     * @param string $templatePath
     * @param string $type
     * @param array<mixed> $config
     * @param array<mixed> $assignToView
     */
    public function renderStandaloneView(
        string $templatePath,
        string $type,
        array $config,
        array $assignToView = []
    ): void {
        $view = $this->getStandaloneView($templatePath, ucfirst($type));

        if (isset($config['file'])) {
            $file = GeneralUtility::getFileAbsFileName(
                $config['file']
            );
            $view->assign('file', $file);
            if (isset($config['width'])) {
                $view->assign('width', $config['width']);
            }
            if (isset($config['height'])) {
                $view->assign('height', $config['height']);
            }
        }
        if ($type === 'page') {
            $view->assign('numPage', $this->getAliasNumPage());
            $view->assign('numPages', $this->getAliasNbPages());
        }

        $view->assignMultiple($assignToView);

        $content = (string)$view->render();
        $content = trim((string)preg_replace('~[\n]+~', '', $content));

        $this->writeHtmlCellWithConfig($content, $config);
    }

    /**
     * Write HTML Cell with configuration
     * @param string $content
     * @param array<mixed> $config
     */
    public function writeHtmlCellWithConfig(string $content, array $config): void
    {
        $width = $config['width'];
        $height = 0;
        if (isset($config['height'])) {
            $height = (int)$config['height'];
        }
        $positionX = $config['positionX'] ?? null;
        $positionY = $config['positionY'] ?? null;
        $align = 'L';
        if (isset($config['align'])) {
            $align = $config['align'];
        }

        $oldFontSize = $this->getFontSizePt();
        if (isset($config['fontSize'])) {
            $this->SetFontSize($config['fontSize']);
        }
        if (isset($config['spacingY'])) {
            $this->setY($this->getY() + $config['spacingY']);
        }

        $this->writeHTMLCell(
            $width,
            $height,
            $positionX,
            $positionY,
            $content,
            false,
            2,
            false,
            true,
            $align,
            true
        );

        if (isset($config['fontSize'])) {
            $this->SetFontSize($oldFontSize);
        }
    }

    /**
     * Get standalone View
     */
    public function getStandaloneView(string $templatePath, string $templateFileName = 'Default', string $format = 'html'): StandaloneView
    {
        $templatePathAndFileName = $templatePath . $templateFileName . '.' . $format;

        /** @var StandaloneView $view */
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setFormat($format);

        if ($this->pdfSettings['view']) {
            $view->setLayoutRootPaths($this->pdfSettings['view']['layoutRootPaths']);
            $view->setPartialRootPaths($this->pdfSettings['view']['partialRootPaths']);

            if (is_array($this->pdfSettings['view']['templateRootPaths'] ?? null)) {
                $paths = $this->pdfSettings['view']['templateRootPaths'];
                ksort($paths);
                $paths = array_reverse($paths);

                foreach ($paths as $pathNameValue) {
                    $templateRootPath = GeneralUtility::getFileAbsFileName($pathNameValue);
                    $completePath = $templateRootPath . $templatePathAndFileName;

                    if (file_exists($completePath)) {
                        $view->setTemplatePathAndFilename($completePath);
                        break;
                    }
                }
            }
        }

        if (!$view->getTemplatePathAndFilename()) {
            $logManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class);
            $logger = $logManager->getLogger(self::class);
            $logger->error(
                'Cannot find Template for PdfService',
                [
                    'templateRootPaths' => $this->pdfSettings['view']['templateRootPaths'],
                    'templatePathAndFileName' => $templatePathAndFileName,
                ]
            );
        }

        return $view;
    }
}

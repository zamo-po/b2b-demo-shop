<?php

namespace Pyz\Zed\Installer\Business;

use Pyz\Zed\Category\Business\Manager\NodeUrlManager;
use Pyz\Zed\Installer\Business\Icecat\Importer\Category\CategoryHierarchyImporter;
use Pyz\Zed\Installer\Business\Icecat\IcecatDataInstaller;
use Pyz\Zed\Installer\Business\Icecat\IcecatLocaleManager;
use Pyz\Zed\Installer\Business\Icecat\Importer\Category\CategoryImporter;
use Pyz\Zed\Installer\Business\Icecat\Importer\Category\CategoryRootImporter;
use Pyz\Zed\Installer\Business\Icecat\Importer\Product\ProductAbstractImporter;
use Pyz\Zed\Installer\Business\Icecat\Importer\Product\ProductCategoryImporter;
use Pyz\Zed\Installer\Business\Icecat\Importer\Product\ProductPriceImporter;
use Pyz\Zed\Installer\Business\Icecat\Importer\Product\ProductSearchImporter;
use Pyz\Zed\Installer\Business\Icecat\Importer\Product\ProductStockImporter;
use Pyz\Zed\Installer\Business\Icecat\Installer\CategoryHierarchyInstaller;
use Pyz\Zed\Installer\Business\Icecat\Installer\CategoryInstaller;
use Pyz\Zed\Installer\Business\Icecat\Installer\CategoryRootInstaller;
use Pyz\Zed\Installer\Business\Icecat\Installer\ProductInstaller;
use Pyz\Zed\Installer\Business\Icecat\Installer\ProductSearchInstaller;
use Pyz\Zed\Installer\Business\Reader\CsvReader;
use Pyz\Zed\Installer\InstallerConfig;
use Pyz\Zed\Installer\InstallerDependencyProvider;
use Spryker\Zed\Category\Business\Generator\UrlPathGenerator;
use Spryker\Zed\Category\Business\TransferGenerator;
use Spryker\Zed\Category\Business\Tree\CategoryTreeReader;
use Spryker\Zed\Category\Business\Tree\CategoryTreeWriter;
use Spryker\Zed\Category\Business\Tree\ClosureTableWriter;
use Spryker\Zed\Category\Business\Tree\Formatter\CategoryTreeFormatter;
use Spryker\Zed\Category\Business\Tree\NodeWriter;
use Spryker\Zed\Installer\Business\InstallerBusinessFactory as SprykerInstallerBusinessFactory;
use Spryker\Zed\Product\Business\Attribute\AttributeManager;
use Spryker\Zed\ProductSearch\Business\Operation\OperationManager;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Pyz\Zed\Installer\InstallerConfig getConfig()
 */
class InstallerBusinessFactory extends SprykerInstallerBusinessFactory
{

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\IcecatInstallerInterface[]
     */
    public function getIcecatDataInstallerCollection()
    {
        return [
            InstallerConfig::RESOURCE_CATEGORY_ROOT => $this->getCategoryRootInstaller(),
            InstallerConfig::RESOURCE_CATEGORY => $this->getCategoryInstaller(),
            InstallerConfig::RESOURCE_CATEGORY_HIERARCHY => $this->getCategoryHierarchyInstaller(),
            InstallerConfig::RESOURCE_PRODUCT => $this->getProductInstaller(),
            InstallerConfig::RESOURCE_PRODUCT_SEARCH => $this->getProductSearchInstaller(),
        ];
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\IcecatInstallerInterface[]
     */
    public function getIcecatImporterCategoryCollection()
    {
        return [
            InstallerConfig::RESOURCE_CATEGORY => $this->getCategoryImporter(),
        ];
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\IcecatInstallerInterface[]
     */
    public function getIcecatImporterCategoryHierarchyCollection()
    {
        return [
            InstallerConfig::RESOURCE_CATEGORY_HIERARCHY => $this->getCategoryHierarchyImporter(),
        ];
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\IcecatInstallerInterface[]
     */
    public function getIcecatImporterCategoryRootCollection()
    {
        return [
            InstallerConfig::RESOURCE_CATEGORY_ROOT => $this->getCategoryRootImporter(),
        ];
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\IcecatInstallerInterface[]
     */
    public function getIcecatImporterProductCollection()
    {
        return [
            InstallerConfig::RESOURCE_PRODUCT => $this->getProductAbstractImporter(),
            InstallerConfig::RESOURCE_PRODUCT_CATEGORY => $this->getProductCategoryImporter(),
            InstallerConfig::RESOURCE_PRODUCT_STOCK => $this->getProductStockImporter(),
            //InstallerConfig::RESOURCE_PRODUCT_PRICE => $this->getProductPriceImporter(),
        ];
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\IcecatInstallerInterface[]
     */
    public function getIcecatImporterProductSearchCollection()
    {
        return [
            InstallerConfig::RESOURCE_PRODUCT_SEARCH => $this->getProductSearchImporter(),
        ];
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Importer\Category\CategoryImporter
     */
    protected function getCategoryImporter()
    {
        $categoryImporter = new CategoryImporter(
            $this->getCsvReader(), $this->getIcecatLocaleManager()
        );

        $categoryImporter->setCategoryFacade($this->getCategoryFacade());
        $categoryImporter->setCategoryQueryContainer($this->getCategoryQueryContainer());
        $categoryImporter->setTouchFacade($this->getTouchFacade());
        $categoryImporter->setUrlFacade($this->getUrlFacade());
        $categoryImporter->setNodeUrlManager($this->createNodeUrlManager());
        $categoryImporter->setCategoryQueryContainer($this->getCategoryQueryContainer());

        return $categoryImporter;
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Importer\Category\CategoryImporter
     */
    protected function getCategoryHierarchyImporter()
    {
        $categoryHierarchyImporter = new CategoryHierarchyImporter(
            $this->getCsvReader(), $this->getIcecatLocaleManager()
        );

        $categoryHierarchyImporter->setCategoryFacade($this->getCategoryFacade());
        $categoryHierarchyImporter->setCategoryQueryContainer($this->getCategoryQueryContainer());
        $categoryHierarchyImporter->setCategoryQueryContainer($this->getCategoryQueryContainer());

        return $categoryHierarchyImporter;
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Importer\Category\CategoryRootImporter
     */
    protected function getCategoryRootImporter()
    {
        $categoryRootImporter = new CategoryRootImporter(
            $this->getCsvReader(), $this->getIcecatLocaleManager()
        );

        $categoryRootImporter->setCategoryFacade($this->getCategoryFacade());
        $categoryRootImporter->setCategoryQueryContainer($this->getCategoryQueryContainer());
        $categoryRootImporter->setTouchFacade($this->getTouchFacade());
        $categoryRootImporter->setUrlFacade($this->getUrlFacade());
        $categoryRootImporter->setNodeUrlManager($this->createNodeUrlManager());
        $categoryRootImporter->setCategoryQueryContainer($this->getCategoryQueryContainer());

        return $categoryRootImporter;
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Importer\Product\ProductCategoryImporter
     */
    protected function getProductCategoryImporter()
    {
        $productCategoryImporter = new ProductCategoryImporter(
            $this->getCsvReader(), $this->getIcecatLocaleManager()
        );

        $productCategoryImporter->setCategoryFacade($this->getCategoryFacade());
        $productCategoryImporter->setCategoryQueryContainer($this->getCategoryQueryContainer());
        $productCategoryImporter->setProductFacade($this->getProductFacade());
        $productCategoryImporter->setProductQueryContainer($this->getProductQueryContainer());
        $productCategoryImporter->setProductCategoryFacade($this->getProductCategoryFacade());
        $productCategoryImporter->setProductCategoryQueryContainer($this->getProductCategoryQueryContainer());
        $productCategoryImporter->setTouchFacade($this->getTouchFacade());

        return $productCategoryImporter;
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Importer\Product\ProductAbstractImporter
     */
    protected function getProductAbstractImporter()
    {
        $productAbstractImporter = new ProductAbstractImporter(
            $this->getCsvReader(), $this->getIcecatLocaleManager()
        );

        $productAbstractImporter->setAttributeManager($this->createAttributeManager());
        $productAbstractImporter->setProductFacade($this->getProductFacade());

        return $productAbstractImporter;
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Importer\Product\ProductPriceImporter
     */
    protected function getProductPriceImporter()
    {
        $productPriceImporter = new ProductPriceImporter(
            $this->getCsvReader(), $this->getIcecatLocaleManager()
        );

        $productPriceImporter->setProductQueryContainer($this->getProductQueryContainer());
        $productPriceImporter->setStockFacade($this->getStockFacade());
        $productPriceImporter->setPriceQueryContainer($this->getPriceQueryContainer());

        return $productPriceImporter;
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Importer\Product\ProductStockImporter
     */
    protected function getProductStockImporter()
    {
        $productStockImporter = new ProductStockImporter(
            $this->getCsvReader(), $this->getIcecatLocaleManager()
        );

        $productStockImporter->setProductQueryContainer($this->getProductQueryContainer());
        $productStockImporter->setStockFacade($this->getStockFacade());

        return $productStockImporter;
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Importer\Product\ProductSearchImporter
     */
    protected function getProductSearchImporter()
    {
        $productSearchImporter = new ProductSearchImporter(
            $this->getCsvReader(), $this->getIcecatLocaleManager()
        );

        $productSearchImporter->setProductSearchFacade($this->getProductSearchFacade());
        $productSearchImporter->setOperationManager($this->createOperationManager());

        return $productSearchImporter;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Pyz\Zed\Installer\Business\Icecat\IcecatDataInstaller
     */
    public function getIcecatDataInstaller(OutputInterface $output)
    {
        return new IcecatDataInstaller(
            $output, $this->getIcecatDataInstallerCollection()
        );
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Installer\CategoryInstaller
     */
    protected function getCategoryInstaller()
    {
        $categoryInstaller = new CategoryInstaller(
            $this->getCsvReader(), $this->getIcecatImporterCategoryCollection()
        );

        return $categoryInstaller;
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Installer\CategoryHierarchyInstaller
     */
    protected function getCategoryHierarchyInstaller()
    {
        $categoryHierarchyInstaller = new CategoryHierarchyInstaller(
            $this->getCsvReader(), $this->getIcecatImporterCategoryHierarchyCollection()
        );

        return $categoryHierarchyInstaller;
    }


    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Installer\CategoryRootInstaller
     */
    protected function getCategoryRootInstaller()
    {
        $categoryRootInstaller = new CategoryRootInstaller(
            $this->getCsvReader(), $this->getIcecatImporterCategoryRootCollection()
        );

        return $categoryRootInstaller;
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Installer\ProductInstaller
     */
    protected function getProductInstaller()
    {
        $productInstaller = new ProductInstaller(
            $this->getCsvReader(), $this->getIcecatImporterProductCollection()
        );

        return $productInstaller;
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Icecat\Installer\ProductSearchInstaller
     */
    protected function getProductSearchInstaller()
    {
        $productSearchInstaller = new ProductSearchInstaller(
            $this->getCsvReader(), $this->getIcecatImporterProductSearchCollection()
        );

        return $productSearchInstaller;
    }

    /**
     * @return \Pyz\Zed\Installer\Business\Reader\CsvReaderInterface
     */
    public function getCsvReader()
    {
        return new CsvReader(
            $this->getConfig()->getIcecatDataPath()
        );
    }

    /**
     * @throws \Spryker\Zed\Kernel\Exception\Container\ContainerKeyNotFoundException
     *
     * @return IcecatLocaleManager
     */
    public function getIcecatLocaleManager()
    {
        return new IcecatLocaleManager(
            $this->getLocaleFacade()
        );
    }

    /**
     * @return \Spryker\Zed\Product\Business\Attribute\AttributeManagerInterface
     */
    protected function createAttributeManager()
    {
        return new AttributeManager(
            $this->getProductQueryContainer()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Persistence\CategoryQueryContainerInterface
     */
    protected function getCategoryQueryContainer()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::QUERY_CONTAINER_CATEGORY);
    }

    /**
     * @return \Spryker\Zed\Product\Persistence\ProductQueryContainerInterface
     */
    protected function getProductQueryContainer()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::QUERY_CONTAINER_PRODUCT);
    }

    /**
     * @return \Spryker\Zed\ProductSearch\Persistence\ProductSearchQueryContainerInterface
     */
    protected function getProductSearchQueryContainer()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::QUERY_CONTAINER_PRODUCT_SEARCH);
    }

    /**
     * @return \Spryker\Zed\ProductCategory\Persistence\ProductCategoryQueryContainerInterface
     */
    protected function getProductCategoryQueryContainer()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::QUERY_CONTAINER_PRODUCT_CATEGORY);
    }

    /**
     * @return \Spryker\Zed\Price\Persistence\PriceQueryContainerInterface
     */
    protected function getPriceQueryContainer()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::QUERY_CONTAINER_PRICE);
    }

    /**
     * @return \Pyz\Zed\Stock\Business\StockFacadeInterface
     */
    protected function getStockFacade()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::FACADE_STOCK);
    }

    /**
     * @return \Pyz\Zed\ProductCategory\Business\ProductCategoryFacadeInterface
     */
    protected function getProductCategoryFacade()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::FACADE_PRODUCT_CATEGORY);
    }

    /**
     * @return \Pyz\Zed\Category\Business\CategoryFacadeInterface
     */
    protected function getCategoryFacade()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::FACADE_CATEGORY);
    }

    /**
     * @return \Pyz\Zed\Product\Business\ProductFacadeInterface
     */
    protected function getProductFacade()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::FACADE_PRODUCT);
    }

    /**
     * @return \Spryker\Zed\Touch\Business\TouchFacadeInterface
     */
    protected function getTouchFacade()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::FACADE_TOUCH);
    }

    /**
     * @return \Spryker\Zed\Locale\Business\LocaleFacadeInterface
     */
    protected function getLocaleFacade()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::FACADE_LOCALE);
    }

    /**
     * @return \Spryker\Zed\Url\Business\UrlFacadeInterface
     */
    protected function getUrlFacade()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::FACADE_URL);
    }

    /**
     * @return \Pyz\Zed\ProductSearch\Business\ProductSearchFacadeInterface
     */
    protected function getProductSearchFacade()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::FACADE_PRODUCT_SEARCH);
    }

    /**
     * @return \Spryker\Zed\Category\Dependency\Facade\CategoryToTouchInterface
     */
    protected function getCategoryToTouchBridge()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::BRIDGE_CATEGORY_TO_TOUCH);
    }

    /**
     * @return \Spryker\Zed\Category\Dependency\Facade\CategoryToUrlInterface
     */
    protected function getCategoryToUrlBridge()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::BRIDGE_CATEGORY_TO_URL);
    }

    /**
     * @return \Spryker\Zed\Category\Dependency\Facade\CategoryToLocaleInterface
     */
    protected function getCategoryToLocaleBridge()
    {
        return $this->getProvidedDependency(InstallerDependencyProvider::BRIDGE_CATEGORY_TO_LOCALE);
    }

    /**
     * @return \Pyz\Zed\Category\Business\Manager\NodeUrlManager
     */
    protected function createNodeUrlManager()
    {
        return new NodeUrlManager(
            $this->createCategoryTreeReader(),
            $this->createUrlPathGenerator(),
            $this->getCategoryToUrlBridge(),
            $this->getCategoryQueryContainer(),
            $this->getProvidedDependency(InstallerDependencyProvider::QUERY_CONTAINER_LOCALE)
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Tree\CategoryTreeWriter
     */
    public function createCategoryTreeWriter()
    {
        return new CategoryTreeWriter(
            $this->createNodeWriter(),
            $this->createClosureTableWriter(),
            $this->createCategoryTreeReader(),
            $this->createNodeUrlManager(),
            $this->getCategoryToTouchBridge(),
            $this->getQueryContainer()->getConnection()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Tree\CategoryTreeReader
     */
    public function createCategoryTreeReader()
    {
        return new CategoryTreeReader(
            $this->getCategoryQueryContainer(),
            $this->createCategoryTreeFormatter()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Tree\NodeWriterInterface
     */
    public function createNodeWriter()
    {
        return new NodeWriter(
            $this->getCategoryQueryContainer()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Generator\UrlPathGeneratorInterface
     */
    public function createUrlPathGenerator()
    {
        return new UrlPathGenerator();
    }

    /**
     * @param array $category
     *
     * @return \Spryker\Zed\Category\Business\Tree\Formatter\CategoryTreeFormatter
     */
    public function createCategoryTreeStructure(array $category)
    {
        return new CategoryTreeFormatter($category);
    }

    /**
     * @return \Spryker\Zed\Category\Business\Tree\ClosureTableWriterInterface
     */
    protected function createClosureTableWriter()
    {
        return new ClosureTableWriter(
            $this->getCategoryQueryContainer()
        );
    }

    /**
     * @return \Spryker\Zed\Category\Business\Tree\Formatter\CategoryTreeFormatter
     */
    protected function createCategoryTreeFormatter()
    {
        return new CategoryTreeFormatter();
    }

    /**
     * @return \Spryker\Zed\Category\Business\TransferGeneratorInterface
     */
    protected function createCategoryTransferGenerator()
    {
        return new TransferGenerator();
    }

    /**
     * @return \Spryker\Zed\ProductSearch\Business\Operation\OperationManagerInterface
     */
    protected function createOperationManager()
    {
        return new OperationManager(
            $this->getProductSearchQueryContainer()
        );
    }

}

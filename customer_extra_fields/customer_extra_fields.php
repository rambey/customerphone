<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
use PrestaShop\PrestaShop\Core\ConstraintValidator\Constraints\CleanHtml;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
if (!defined('_PS_VERSION_')) {
    exit;
}
class Customer_extra_fields extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'customer_extra_fields';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Evolutive Group';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Customer Extra Field');
        $this->description = $this->l('add extra field to customer');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {        
        /**
         * check if exist
         */
        if (empty(Db::getInstance()->executeS("SHOW COLUMNS FROM " . _DB_PREFIX_ . "customer LIKE 'phone'"))) {
            Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'customer` ADD `phone` varchar(250);');
        }
        return parent::install() &&
        $this->registerHook('actionCustomerFormBuilderModifier') &&
        $this->registerHook('actionAfterCreateCustomerFormHandler') &&
        $this->registerHook('actionAfterUpdateCustomerFormHandler') && 
        $this->registerHook('actionAdminCustomersListingFieldsModifier') &&
        $this->registerHook('actionCustomerGridDefinitionModifier') &&
        $this->registerHook('actionCustomerGridQueryBuilderModifier');       
    }

    public function uninstall()
    {
        return parent::uninstall();
    }
    public function hookActionCustomerFormBuilderModifier(array $params)
    {
       $formBuilder = $params['form_builder'];
        $formBuilder->add('phone',\Symfony\Component\Form\Extension\Core\Type\TextType::class, [
            'label' => $this->l('Phone Number'),
            'required' => false,
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\Length([
                        'max' => 20,
                        'maxMessage' => $this->l('Max caracters allowed : 20'),
                    ]),
              
            ],
            //'data' => 'test valeur' 

        ]);
     
        $customerForm = new Customer((int)$params['id']);
        $params['data']['phone'] = $customerForm->phone;
        $formBuilder->setData($params['data']);
    }

    public function hookActionBefpreCreateCustomerFormHandler(array $params)
    {
        $this->updateData($params['form_data'], $params);
    }

    public function hookActionAfterCreateCustomerFormHandler(array $params)
    {
        $this->updateData($params['form_data'], $params);
    }

    public function hookActionBeforeUpdateCustomerFormHandler(array $params)
    {
        $this->updateData($params['form_data'], $params);
    }

    public function hookActionAfterUpdateCustomerFormHandler(array $params)
    {
        $this->updateData($params['form_data'], $params);
    }

    protected function updateData(array $data, $params)
    {
        $customerFormData = new Customer((int)$params['id']);
        $customerFormData->phone = $data['phone'];
        $customerFormData->save();
    }
        public function hookActionAdminCustomersListingFieldsModifier($params)
    {
        $params['fields']['phone'] = array(
            'title' => $this->l('phone'),
            'align' => 'center',
        );
    }
    public function hookActionCustomerGridDefinitionModifier(array $params)
{
    /** @var GridDefinitionInterface $definition */
    $definition = $params['definition'];

    $definition
        ->getColumns()
        ->addAfter(
            'optin',
            (new DataColumn('phone'))
                ->setName($this->l('phone'))
                ->setOptions([
                    'field' => 'phone',
                ])
        )
    ;
    // For search filter
    
    $definition->getFilters()->add(
        (new Filter('phone', TextType::class))
        ->setAssociatedColumn('phone')
    );
}
	public function hookActionCustomerGridQueryBuilderModifier(array $params)
    {
        /** @var QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        /** @var CustomerFilters $searchCriteria */
        $searchCriteria = $params['search_criteria'];

        $searchQueryBuilder->addSelect(
            'IF(wcm.`phone` IS NULL,0,wcm.`phone`) AS `phone`'
        );

        $searchQueryBuilder->leftJoin(
            'c',
            '`' . pSQL(_DB_PREFIX_) . 'customer`',
            'wcm',
            'wcm.`id_customer` = c.`id_customer`'
        );

        if ('phone' === $searchCriteria->getOrderBy()) {
            $searchQueryBuilder->orderBy('wcm.`phone`', $searchCriteria->getOrderWay());
        }

        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ('phone' === $filterName) {
                $searchQueryBuilder->andWhere('wcm.`phone` = :phone');
                $searchQueryBuilder->setParameter('phone', $filterValue);

                if (!$filterValue) {
                    $searchQueryBuilder->orWhere('wcm.`phone` IS NULL');
                }
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace WechatOfficialAccountServerMessageBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use WechatOfficialAccountBundle\Entity\Account;
use WechatOfficialAccountServerMessageBundle\Entity\ServerMessage;

/**
 * @extends AbstractCrudController<ServerMessage>
 */
#[AdminCrud(routePath: '/wechat-official-account/server-message', routeName: 'wechat_official_account_server_message')]
#[Autoconfigure(public: true)]
final class ServerMessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ServerMessage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('服务端消息')
            ->setEntityLabelInPlural('服务端消息管理')
            ->setPageTitle('index', '服务端消息列表')
            ->setPageTitle('detail', '服务端消息详情')
            ->setPageTitle('new', '创建服务端消息')
            ->setPageTitle('edit', '编辑服务端消息')
            ->setHelp('index', '管理微信公众号接收到的服务端消息')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['msgId', 'fromUserName', 'toUserName', 'msgType'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        // 作为只读日志实体，表单不展示任何可编辑字段
        yield IdField::new('id', 'ID')->hideOnForm();

        yield AssociationField::new('account', '公众号账号')
            ->setRequired(true)
            ->setHelp('关联的微信公众号账号')
            ->hideOnForm()
            ->formatValue(function ($value) {
                if ($value instanceof Account) {
                    return $value->getName();
                }

                return '-';
            })
        ;

        yield TextField::new('msgId', '消息ID')
            ->setRequired(true)
            ->setMaxLength(64)
            ->setHelp('微信消息唯一标识符')
            ->hideOnForm()
        ;

        yield TextField::new('msgType', '消息类型')
            ->setRequired(true)
            ->setMaxLength(30)
            ->setHelp('消息类型，如text、image、voice等')
            ->hideOnForm()
        ;

        yield TextField::new('fromUserName', '发送用户')
            ->setRequired(true)
            ->setMaxLength(64)
            ->setHelp('消息发送方OpenID')
            ->hideOnForm()
        ;

        yield TextField::new('toUserName', '接收用户')
            ->setRequired(true)
            ->setMaxLength(64)
            ->setHelp('消息接收方（公众号原始ID）')
            ->hideOnForm()
        ;

        yield IntegerField::new('createTime', '消息时间戳')
            ->setHelp('微信服务器推送的消息创建时间戳')
            ->hideOnIndex()
            ->hideOnForm()
            ->formatValue(function ($value, $entity) {
                if ($entity instanceof ServerMessage && null !== $entity->getCreateTime()) {
                    return $entity->getCreateTime();
                }

                return 0;
            })
        ;

        yield TextField::new('createTimeFormatted', '消息时间')
            ->setHelp('消息创建时间（根据时间戳计算）')
            ->onlyOnIndex()
        ;

        yield CodeEditorField::new('context', '消息内容')
            ->setLanguage('javascript')
            ->setHelp('完整的消息JSON数据')
            ->hideOnIndex()
            ->hideOnForm()
            ->formatValue(function ($value) {
                if (null === $value || !is_array($value)) {
                    return '{}';
                }

                return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            })
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            // 该实体为只读日志，不允许新建或编辑
            ->disable(Action::NEW, Action::EDIT)
            // 删除操作仅限管理员，如需完全只读可改为同时禁用 DELETE
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('account', '公众号账号'))
            ->add(TextFilter::new('msgType', '消息类型'))
            ->add(TextFilter::new('fromUserName', '发送用户'))
            ->add(TextFilter::new('toUserName', '接收用户'))
            ->add(TextFilter::new('msgId', '消息ID'))
            ->add(DateTimeFilter::new('createTime', '消息时间'))
        ;
    }
}

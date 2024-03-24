<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324102617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA mail');
        $this->addSql('CREATE TABLE mail.domain (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, is_works BOOLEAN NOT NULL, check_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, fail_check_count INT DEFAULT NULL, creator UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, editor UUID DEFAULT NULL, edited_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__domain_is_works ON mail.domain (is_works)');
        $this->addSql('CREATE INDEX idx__domain_check_date ON mail.domain (check_date)');
        $this->addSql('CREATE INDEX idx__domain_fail_check_count ON mail.domain (fail_check_count)');
        $this->addSql('CREATE INDEX idx__domain_creator ON mail.domain (creator)');
        $this->addSql('CREATE INDEX idx__domain_created_at ON mail.domain (created_at)');
        $this->addSql('CREATE INDEX idx__domain_editor ON mail.domain (editor)');
        $this->addSql('CREATE INDEX idx__domain_edited_at ON mail.domain (edited_at)');
        $this->addSql('CREATE UNIQUE INDEX idx__domain_name ON mail.domain (name)');
        $this->addSql('COMMENT ON TABLE mail.domain IS \'Домены получателей\'');
        $this->addSql('COMMENT ON COLUMN mail.domain.id IS \'ID домена\'');
        $this->addSql('COMMENT ON COLUMN mail.domain.name IS \'Наименование\'');
        $this->addSql('COMMENT ON COLUMN mail.domain.is_works IS \'Флаг, что домен рабочий\'');
        $this->addSql('COMMENT ON COLUMN mail.domain.check_date IS \'Дата проверки домена(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail.domain.fail_check_count IS \'Количество ошибок проверки\'');
        $this->addSql('COMMENT ON COLUMN mail.domain.creator IS \'UUID сотрудника, создавшего домен(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.domain.created_at IS \'Дата создания домена(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail.domain.editor IS \'UUID Сотрудника изменившего домен(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.domain.edited_at IS \'Дата последнего изменения(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE mail.domain_history (id SERIAL NOT NULL, domain_id INT NOT NULL, changes JSON NOT NULL, editor UUID NOT NULL, edited_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__domain_history_domain_id ON mail.domain_history (domain_id)');
        $this->addSql('CREATE INDEX idx__domain_history_editor ON mail.domain_history (editor)');
        $this->addSql('CREATE INDEX idx__domain_history_edited_at ON mail.domain_history (edited_at)');
        $this->addSql('COMMENT ON TABLE mail.domain_history IS \'История изменения доменов\'');
        $this->addSql('COMMENT ON COLUMN mail.domain_history.domain_id IS \'ID домена\'');
        $this->addSql('COMMENT ON COLUMN mail.domain_history.changes IS \'Массив с изменениями\'');
        $this->addSql('COMMENT ON COLUMN mail.domain_history.editor IS \'UUID сотрудника, редактировавшего домен(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.domain_history.edited_at IS \'Когда сохранены изменения(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE mail.file (id SERIAL NOT NULL, filename VARCHAR(255) NOT NULL, file_size INT NOT NULL, mime_type VARCHAR(255) NOT NULL, hash VARCHAR(32) NOT NULL, upload_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX idx__file_hash ON mail.file (hash)');
        $this->addSql('COMMENT ON TABLE mail.file IS \'Файлы(вложения)\'');
        $this->addSql('COMMENT ON COLUMN mail.file.filename IS \'Название\'');
        $this->addSql('COMMENT ON COLUMN mail.file.file_size IS \'Размер\'');
        $this->addSql('COMMENT ON COLUMN mail.file.mime_type IS \'Тип Mime\'');
        $this->addSql('COMMENT ON COLUMN mail.file.hash IS \'Хэш\'');
        $this->addSql('COMMENT ON COLUMN mail.file.upload_at IS \'Дата загрузки(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail.file.deleted_at IS \'Дата удаления(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE mail."group" (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, creator UUID NOT NULL, editor UUID DEFAULT NULL, deleter UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, edited_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__group_name ON mail."group" (name)');
        $this->addSql('CREATE INDEX idx__group_creator ON mail."group" (creator)');
        $this->addSql('CREATE INDEX idx__group_editor ON mail."group" (editor)');
        $this->addSql('CREATE INDEX idx__group_deleter ON mail."group" (deleter)');
        $this->addSql('CREATE INDEX idx__group_created_at ON mail."group" (created_at)');
        $this->addSql('CREATE INDEX idx__group_edited_at ON mail."group" (edited_at)');
        $this->addSql('CREATE INDEX idx__group_deleted_at ON mail."group" (deleted_at)');
        $this->addSql('COMMENT ON TABLE mail."group" IS \'Группы получателей писем\'');
        $this->addSql('COMMENT ON COLUMN mail."group".name IS \'Название группы\'');
        $this->addSql('COMMENT ON COLUMN mail."group".creator IS \'UUID создавшего группу(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail."group".editor IS \'UUID обновившего группу(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail."group".deleter IS \'UUID удалившего группу(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail."group".created_at IS \'Дата создания(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail."group".edited_at IS \'Дата обновления(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail."group".deleted_at IS \'Дата удаления(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE mail.group_history (id SERIAL NOT NULL, group_id INT NOT NULL, changes JSON NOT NULL, editor UUID NOT NULL, edited_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__group_history_group_id ON mail.group_history (group_id)');
        $this->addSql('CREATE INDEX idx__group_history_editor ON mail.group_history (editor)');
        $this->addSql('CREATE INDEX idx__group_history_edited_at ON mail.group_history (edited_at)');
        $this->addSql('COMMENT ON TABLE mail.group_history IS \'История изменения групп\'');
        $this->addSql('COMMENT ON COLUMN mail.group_history.group_id IS \'ID группы\'');
        $this->addSql('COMMENT ON COLUMN mail.group_history.changes IS \'Массив с изменениями\'');
        $this->addSql('COMMENT ON COLUMN mail.group_history.editor IS \'UUID сотрудника, редактировавшего группу(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.group_history.edited_at IS \'Когда сохранены изменения(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE mail.letter (id SERIAL NOT NULL, template INT NOT NULL, subject VARCHAR(255) DEFAULT NULL, content TEXT DEFAULT NULL, form VARCHAR(255) NOT NULL, recipient INT NOT NULL, values JSON DEFAULT NULL, creator UUID NOT NULL, editor UUID DEFAULT NULL, deleter UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, edited_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, sender UUID DEFAULT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, status VARCHAR(255) DEFAULT \'NOT_SENT\', PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__letter_subject ON mail.letter (subject)');
        $this->addSql('CREATE INDEX idx__letter_form ON mail.letter (form)');
        $this->addSql('CREATE INDEX idx__letter_template ON mail.letter (template)');
        $this->addSql('CREATE INDEX idx__letter_recipient ON mail.letter (recipient)');
        $this->addSql('CREATE INDEX idx__letter_creator ON mail.letter (creator)');
        $this->addSql('CREATE INDEX idx__letter_editor ON mail.letter (editor)');
        $this->addSql('CREATE INDEX idx__letter_deleter ON mail.letter (deleter)');
        $this->addSql('CREATE INDEX idx__letter_created_at ON mail.letter (created_at)');
        $this->addSql('CREATE INDEX idx__letter_edited_at ON mail.letter (edited_at)');
        $this->addSql('CREATE INDEX idx__letter_deleted_at ON mail.letter (deleted_at)');
        $this->addSql('CREATE INDEX idx__letter_sender ON mail.letter (sender)');
        $this->addSql('CREATE INDEX idx__letter_sent_at ON mail.letter (sent_at)');
        $this->addSql('CREATE INDEX idx__letter_status ON mail.letter (status)');
        $this->addSql('COMMENT ON TABLE mail.letter IS \'Данные писем для отправки\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.id IS \'Идентификатор\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.template IS \'Шаблон\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.subject IS \'Название (тема)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.content IS \'Содержимое (тело)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.form IS \'Тип\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.recipient IS \'Получатель(и)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.values IS \'Значения для шаблонов\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.creator IS \'Кто создал письмо(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.editor IS \'Кто изменил письмо(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.deleter IS \'Кто удалил письмо(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.created_at IS \'Дата создания(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.edited_at IS \'Дата изменения(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.deleted_at IS \'Дата удаления(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.sender IS \'Отправитель письма(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.sent_at IS \'Дата и время отправления(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter.status IS \'Статус отправки\'');
        $this->addSql('CREATE TABLE mail.letter_smtp (letter_id INT NOT NULL, smtp_id INT NOT NULL, PRIMARY KEY(letter_id, smtp_id))');
        $this->addSql('CREATE INDEX IDX_8F16CA8F4525FF26 ON mail.letter_smtp (letter_id)');
        $this->addSql('CREATE INDEX IDX_8F16CA8FAEBF3FD ON mail.letter_smtp (smtp_id)');
        $this->addSql('COMMENT ON TABLE mail.letter_smtp IS \'Связующая таблица писем с серверами отправлений\'');
        $this->addSql('COMMENT ON COLUMN mail.letter_smtp.letter_id IS \'Письмо\'');
        $this->addSql('COMMENT ON COLUMN mail.letter_smtp.smtp_id IS \'Сервер отправления\'');
        $this->addSql('CREATE TABLE mail.letter_file (letter_id INT NOT NULL, file_id INT NOT NULL, PRIMARY KEY(letter_id, file_id))');
        $this->addSql('CREATE INDEX IDX_1EB94C4525FF26 ON mail.letter_file (letter_id)');
        $this->addSql('CREATE INDEX IDX_1EB94C93CB796C ON mail.letter_file (file_id)');
        $this->addSql('COMMENT ON TABLE mail.letter_file IS \'Связующая таблица писем с файлами(вложениями)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter_file.letter_id IS \'Письмо\'');
        $this->addSql('COMMENT ON COLUMN mail.letter_file.file_id IS \'Файл\'');
        $this->addSql('CREATE TABLE mail.letter_history (id SERIAL NOT NULL, letter_id INT NOT NULL, changes JSON NOT NULL, editor UUID NOT NULL, edited_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__letter_history_letter_id ON mail.letter_history (letter_id)');
        $this->addSql('CREATE INDEX idx__letter_history_editor ON mail.letter_history (editor)');
        $this->addSql('CREATE INDEX idx__letter_history_edited_at ON mail.letter_history (edited_at)');
        $this->addSql('COMMENT ON TABLE mail.letter_history IS \'История изменения писем\'');
        $this->addSql('COMMENT ON COLUMN mail.letter_history.letter_id IS \'ID письма\'');
        $this->addSql('COMMENT ON COLUMN mail.letter_history.changes IS \'Массив с изменениями\'');
        $this->addSql('COMMENT ON COLUMN mail.letter_history.editor IS \'UUID сотрудника, редактировавшего письмо(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.letter_history.edited_at IS \'Когда сохранены изменения(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE mail.mailing_list (id SERIAL NOT NULL, letter_id INT NOT NULL, recipient_id INT NOT NULL, smtp_id INT DEFAULT NULL, is_sent BOOLEAN DEFAULT false NOT NULL, is_delivered BOOLEAN DEFAULT false NOT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__mailing_list_letter_id ON mail.mailing_list (letter_id)');
        $this->addSql('CREATE INDEX idx__mailing_list_recipient_id ON mail.mailing_list (recipient_id)');
        $this->addSql('CREATE INDEX idx__mailing_list_smtp_id ON mail.mailing_list (smtp_id)');
        $this->addSql('CREATE INDEX idx__mailing_list_is_sent ON mail.mailing_list (is_sent)');
        $this->addSql('CREATE INDEX idx__mailing_list_is_delivered ON mail.mailing_list (is_delivered)');
        $this->addSql('CREATE INDEX idx__mailing_list_sent_at ON mail.mailing_list (sent_at)');
        $this->addSql('CREATE UNIQUE INDEX idx__mailing_list_letter_recipient ON mail.mailing_list (letter_id, recipient_id)');
        $this->addSql('COMMENT ON TABLE mail.mailing_list IS \'Данные рассылки\'');
        $this->addSql('COMMENT ON COLUMN mail.mailing_list.letter_id IS \'Письмо\'');
        $this->addSql('COMMENT ON COLUMN mail.mailing_list.recipient_id IS \'Получатель\'');
        $this->addSql('COMMENT ON COLUMN mail.mailing_list.smtp_id IS \'Сервер отправления\'');
        $this->addSql('COMMENT ON COLUMN mail.mailing_list.is_sent IS \'Флаг отправления\'');
        $this->addSql('COMMENT ON COLUMN mail.mailing_list.is_delivered IS \'Флаг доставки\'');
        $this->addSql('COMMENT ON COLUMN mail.mailing_list.sent_at IS \'Дата отправления(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail.mailing_list.comment IS \'Примечания\'');
        $this->addSql('CREATE TABLE mail.recipient (id SERIAL NOT NULL, domain_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, comment TEXT DEFAULT NULL, state VARCHAR(255) DEFAULT \'UNCONFIRMED\', is_consent BOOLEAN NOT NULL, creator UUID NOT NULL, editor UUID DEFAULT NULL, deleter UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, edited_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__recipient_state ON mail.recipient (state)');
        $this->addSql('CREATE INDEX idx__recipient_is_consent ON mail.recipient (is_consent)');
        $this->addSql('CREATE INDEX idx__recipient_creator ON mail.recipient (creator)');
        $this->addSql('CREATE INDEX idx__recipient_editor ON mail.recipient (editor)');
        $this->addSql('CREATE INDEX idx__recipient_deleter ON mail.recipient (deleter)');
        $this->addSql('CREATE INDEX idx__recipient_created_at ON mail.recipient (created_at)');
        $this->addSql('CREATE INDEX idx__recipient_edited_at ON mail.recipient (edited_at)');
        $this->addSql('CREATE INDEX idx__recipient_deleted_at ON mail.recipient (deleted_at)');
        $this->addSql('CREATE INDEX idx__recipient_domain_id ON mail.recipient (domain_id)');
        $this->addSql('CREATE UNIQUE INDEX idx__recipient_email ON mail.recipient (email)');
        $this->addSql('COMMENT ON TABLE mail.recipient IS \'Получатели писем\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient.domain_id IS \'ID домена\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient.email IS \'Email\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient.comment IS \'Комментарий\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient.state IS \'Статус email\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient.is_consent IS \'Согласие на рассылку\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient.creator IS \'Кто создал письмо(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient.editor IS \'Кто изменил письмо(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient.deleter IS \'Кто удалил письмо(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient.created_at IS \'Дата создания(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient.edited_at IS \'Дата обновления(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient.deleted_at IS \'Дата удаления(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE mail.recipient_group (recipient_id INT NOT NULL, group_id INT NOT NULL, PRIMARY KEY(recipient_id, group_id))');
        $this->addSql('CREATE INDEX IDX_A5889F4E92F8F78 ON mail.recipient_group (recipient_id)');
        $this->addSql('CREATE INDEX IDX_A5889F4FE54D947 ON mail.recipient_group (group_id)');
        $this->addSql('COMMENT ON TABLE mail.recipient_group IS \'Связующая таблица получателей с группами\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient_group.recipient_id IS \'Получатель\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient_group.group_id IS \'Группа\'');
        $this->addSql('CREATE TABLE mail.recipient_history (id SERIAL NOT NULL, recipient_id INT NOT NULL, changes JSON NOT NULL, editor UUID NOT NULL, edited_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__recipient_history_recipient_id ON mail.recipient_history (recipient_id)');
        $this->addSql('CREATE INDEX idx__recipient_history_editor ON mail.recipient_history (editor)');
        $this->addSql('CREATE INDEX idx__recipient_history_edited_at ON mail.recipient_history (edited_at)');
        $this->addSql('COMMENT ON TABLE mail.recipient_history IS \'История изменения данных получателя\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient_history.recipient_id IS \'ID получателя\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient_history.changes IS \'Массив с изменениями\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient_history.editor IS \'UUID сотрудника, редактировавшего группу(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.recipient_history.edited_at IS \'Когда сохранены изменения(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE mail.smtp_account (id SERIAL NOT NULL, title VARCHAR(255) DEFAULT NULL, host VARCHAR(45) NOT NULL, login VARCHAR(100) NOT NULL, password VARCHAR(45) NOT NULL, port INT DEFAULT 25 NOT NULL, is_system BOOLEAN NOT NULL, is_deleted BOOLEAN DEFAULT false NOT NULL, is_active BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__smtp_account_system ON mail.smtp_account (is_system)');
        $this->addSql('CREATE INDEX idx__smtp_account_deleted ON mail.smtp_account (is_deleted)');
        $this->addSql('CREATE INDEX idx__smtp_account_active ON mail.smtp_account (is_active)');
        $this->addSql('COMMENT ON TABLE mail.smtp_account IS \'Параметры SMTP-аккаунтов\'');
        $this->addSql('COMMENT ON COLUMN mail.smtp_account.title IS \'Название аккаунта\'');
        $this->addSql('COMMENT ON COLUMN mail.smtp_account.host IS \'Хост\'');
        $this->addSql('COMMENT ON COLUMN mail.smtp_account.login IS \'Логин\'');
        $this->addSql('COMMENT ON COLUMN mail.smtp_account.password IS \'Пароль\'');
        $this->addSql('COMMENT ON COLUMN mail.smtp_account.port IS \'Порт\'');
        $this->addSql('COMMENT ON COLUMN mail.smtp_account.is_system IS \'Аккаунт является системным\'');
        $this->addSql('COMMENT ON COLUMN mail.smtp_account.is_deleted IS \'Флаг удаления\'');
        $this->addSql('COMMENT ON COLUMN mail.smtp_account.is_active IS \'Флаг активного состояния\'');
        $this->addSql('CREATE TABLE mail.smtp_history (id SERIAL NOT NULL, smtp_id INT NOT NULL, changes JSON NOT NULL, editor UUID NOT NULL, edited_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__smtp_history_smtp_id ON mail.smtp_history (smtp_id)');
        $this->addSql('CREATE INDEX idx__smtp_history_editor ON mail.smtp_history (editor)');
        $this->addSql('CREATE INDEX idx__smtp_history_edited_at ON mail.smtp_history (edited_at)');
        $this->addSql('COMMENT ON TABLE mail.smtp_history IS \'История изменения аккаунтов\'');
        $this->addSql('COMMENT ON COLUMN mail.smtp_history.smtp_id IS \'ID группы\'');
        $this->addSql('COMMENT ON COLUMN mail.smtp_history.changes IS \'Массив с изменениями\'');
        $this->addSql('COMMENT ON COLUMN mail.smtp_history.editor IS \'UUID сотрудника, редактировавшего аккаунт(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.smtp_history.edited_at IS \'Когда сохранены изменения(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE mail.template (id SERIAL NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, content TEXT DEFAULT NULL, subject TEXT DEFAULT NULL, variables JSON DEFAULT \'{}\' NOT NULL, creator UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, editor UUID DEFAULT NULL, edited_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleter UUID DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__template_title ON mail.template (title)');
        $this->addSql('CREATE INDEX idx__template_parent_id ON mail.template (parent_id)');
        $this->addSql('CREATE INDEX idx__template_creator ON mail.template (creator)');
        $this->addSql('CREATE INDEX idx__template_created_at ON mail.template (created_at)');
        $this->addSql('CREATE INDEX idx__template_editor ON mail.template (editor)');
        $this->addSql('CREATE INDEX idx__template_edited_at ON mail.template (edited_at)');
        $this->addSql('CREATE INDEX idx__template_deleter ON mail.template (deleter)');
        $this->addSql('CREATE INDEX idx__template_deleted_at ON mail.template (deleted_at)');
        $this->addSql('CREATE UNIQUE INDEX idx__template_title_exists ON mail.template (title, deleter, deleted_at)');
        $this->addSql('COMMENT ON TABLE mail.template IS \'Шаблоны для писем\'');
        $this->addSql('COMMENT ON COLUMN mail.template.parent_id IS \'ID родительского шаблона\'');
        $this->addSql('COMMENT ON COLUMN mail.template.title IS \'Название\'');
        $this->addSql('COMMENT ON COLUMN mail.template.content IS \'Содержимое\'');
        $this->addSql('COMMENT ON COLUMN mail.template.subject IS \'Тема для письма\'');
        $this->addSql('COMMENT ON COLUMN mail.template.variables IS \'Массив с плейсхолдерами (переменными)\'');
        $this->addSql('COMMENT ON COLUMN mail.template.creator IS \'UUID сотрудника, создавшего шаблон(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.template.created_at IS \'Когда создан шаблон(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail.template.editor IS \'UUID сотрудника, редактировавшего шаблон(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.template.edited_at IS \'Когда было предыдущее редактирование(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN mail.template.deleter IS \'UUID сотрудника, кто удалил шаблон(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.template.deleted_at IS \'Когда шаблон был удален(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE mail.template_history (id SERIAL NOT NULL, template_id INT NOT NULL, changes JSON NOT NULL, editor UUID NOT NULL, edited_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx__template_history_template_id ON mail.template_history (template_id)');
        $this->addSql('CREATE INDEX idx__template_history_editor ON mail.template_history (editor)');
        $this->addSql('CREATE INDEX idx__template_history_edited_at ON mail.template_history (edited_at)');
        $this->addSql('COMMENT ON TABLE mail.template_history IS \'История изменения Email шаблонов\'');
        $this->addSql('COMMENT ON COLUMN mail.template_history.template_id IS \'ID шаблона\'');
        $this->addSql('COMMENT ON COLUMN mail.template_history.changes IS \'Массив с изменениями\'');
        $this->addSql('COMMENT ON COLUMN mail.template_history.editor IS \'UUID сотрудника, редактировавшего шаблон(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN mail.template_history.edited_at IS \'Когда сохранены изменения(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE mail.domain_history ADD CONSTRAINT FK_7366063E115F0EE5 FOREIGN KEY (domain_id) REFERENCES mail.domain (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.group_history ADD CONSTRAINT FK_CCF87478FE54D947 FOREIGN KEY (group_id) REFERENCES mail."group" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.letter ADD CONSTRAINT FK_19248AFE97601F83 FOREIGN KEY (template) REFERENCES mail.template (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.letter_smtp ADD CONSTRAINT FK_8F16CA8F4525FF26 FOREIGN KEY (letter_id) REFERENCES mail.letter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.letter_smtp ADD CONSTRAINT FK_8F16CA8FAEBF3FD FOREIGN KEY (smtp_id) REFERENCES mail.smtp_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.letter_file ADD CONSTRAINT FK_1EB94C4525FF26 FOREIGN KEY (letter_id) REFERENCES mail.letter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.letter_file ADD CONSTRAINT FK_1EB94C93CB796C FOREIGN KEY (file_id) REFERENCES mail.file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.letter_history ADD CONSTRAINT FK_64B890214525FF26 FOREIGN KEY (letter_id) REFERENCES mail.letter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.mailing_list ADD CONSTRAINT FK_8A70A7BA4525FF26 FOREIGN KEY (letter_id) REFERENCES mail.letter (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.mailing_list ADD CONSTRAINT FK_8A70A7BAE92F8F78 FOREIGN KEY (recipient_id) REFERENCES mail.recipient (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.mailing_list ADD CONSTRAINT FK_8A70A7BAAEBF3FD FOREIGN KEY (smtp_id) REFERENCES mail.smtp_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.recipient ADD CONSTRAINT FK_9550B8C115F0EE5 FOREIGN KEY (domain_id) REFERENCES mail.domain (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.recipient_group ADD CONSTRAINT FK_A5889F4E92F8F78 FOREIGN KEY (recipient_id) REFERENCES mail.recipient (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.recipient_group ADD CONSTRAINT FK_A5889F4FE54D947 FOREIGN KEY (group_id) REFERENCES mail."group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.recipient_history ADD CONSTRAINT FK_432AE6CE92F8F78 FOREIGN KEY (recipient_id) REFERENCES mail.recipient (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.smtp_history ADD CONSTRAINT FK_15243229AEBF3FD FOREIGN KEY (smtp_id) REFERENCES mail.smtp_account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.template ADD CONSTRAINT FK_AD6F095D727ACA70 FOREIGN KEY (parent_id) REFERENCES mail.template (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mail.template_history ADD CONSTRAINT FK_795CD45A5DA0FB8 FOREIGN KEY (template_id) REFERENCES mail.template (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE mail.domain CASCADE');
        $this->addSql('DROP TABLE mail.domain_history CASCADE');
        $this->addSql('DROP TABLE mail.file CASCADE');
        $this->addSql('DROP TABLE mail."group" CASCADE');
        $this->addSql('DROP TABLE mail.group_history CASCADE');
        $this->addSql('DROP TABLE mail.letter CASCADE');
        $this->addSql('DROP TABLE mail.letter_smtp CASCADE');
        $this->addSql('DROP TABLE mail.letter_file CASCADE');
        $this->addSql('DROP TABLE mail.letter_history CASCADE');
        $this->addSql('DROP TABLE mail.mailing_list CASCADE');
        $this->addSql('DROP TABLE mail.recipient CASCADE');
        $this->addSql('DROP TABLE mail.recipient_group CASCADE');
        $this->addSql('DROP TABLE mail.recipient_history CASCADE');
        $this->addSql('DROP TABLE mail.smtp_account CASCADE');
        $this->addSql('DROP TABLE mail.smtp_history CASCADE');
        $this->addSql('DROP TABLE mail.template CASCADE');
        $this->addSql('DROP TABLE mail.template_history CASCADE');
        $this->addSql('DROP SCHEMA mail CASCADE');
    }
}

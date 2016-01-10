<?php

namespace Syzygy\FrameworkBundle\Mail;

class Composer {
	protected $mailer;
	protected $twig;

	protected $defaultFrom = array();
	protected $defaultSubject = '';
	protected $globalVars = array();

	public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig) {
		$this->mailer = $mailer;
		$this->twig = $twig;
	}

	/**
	 * @param string|array $from
	 */
	public function setDefaultFrom($from) {
		$this->defaultFrom = $from;
	}

	/**
	 * @param string $subject
	 */
	public function setDefaultSubject($subject) {
		$this->defaultSubject = $subject;
	}

	/**
	 * @param array $vars
	 */
	public function setGlobalVars(array $vars) {
		$this->globalVars = $vars;
	}

	/**
	 * Fills in mail template.
	 * Attachments with numeric keys are just regular attachments. Use createAttachedFile to create them.
	 * Attachments with named keys could be referred in template (e.g. embedded images). Use createEmbeddedFile to create them.
	 * @param string $templateName Identifier of a view to use as template.
	 * @param array $vars Variables to use in template.
	 * @param \Swift_Mime_MimeEntity[] $attachments Entities to attach. Named could be addressed as variables.
	 * @return \Swift_Message
	 */
	public function composeFromTemplate($templateName, $vars = array(), $attachments = array()) {
		/** @var \Twig_Template $templateContent */
		$templateContent = $this->twig->loadTemplate($templateName);
		$message = \Swift_Message::newInstance();

		$data = array_merge($this->globalVars, $vars);

		foreach($attachments as $k => $v) {
			if(is_numeric($k)) {
				$message->attach($v);
			} else {
				$data[$k] = $message->embed($v);
			}
		}

		$this->populateMessage($message, $templateContent, $data);

		return $message;
	}

	public function createAttachedFile($filePath) {
		return \Swift_Attachment::fromPath($filePath);
	}

	public function createEmbeddedFile($filePath) {
		return \Swift_EmbeddedFile::fromPath($filePath);
	}

	/**
	 * Fills in message using blocks from a template (`body`, `subject`, `from`).
	 * If there is no `body` block then whole template will be used.
	 * If there is no `subject` or `from` block then corresponding default value will be used.
	 * @param \Swift_Message $message
	 * @param \Twig_Template $templateContent
	 * @param array $data
	 */
	protected function populateMessage(\Swift_Message $message, \Twig_Template $templateContent, $data) {
		$body = $templateContent->hasBlock('body')
			? $templateContent->renderBlock('body', $data)
			: $templateContent->render($data);

		$subject = $templateContent->hasBlock('subject')
			? $templateContent->renderBlock('subject', $data)
			: $this->defaultSubject;

		$from = $templateContent->hasBlock('from')
			? $templateContent->renderBlock('from', $data)
			: $this->defaultFrom;

		$message
			->setFrom($from)
			->setSubject($subject)
			->setBody($body, 'text/html', 'utf-8')
		;
	}

	/**
	 * @param string|array $to
	 * @param string $template
	 * @param array $data
	 * @param array $attachments
	 * @return \Swift_Message
	 */
	public function sendFromTemplate($to, $template, $data=array(), $attachments=array()) {
		$message = $this->composeFromTemplate($template, $data, $attachments);

		$message->setTo($to);

		$this->mailer->send($message);

		return $message;
	}
}

<?php

namespace Oro\Bundle\LocaleBundle\Controller;

use Oro\Bundle\FormBundle\Model\UpdateHandler;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizationType;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD for localizations.
 */
class LocalizationController extends AbstractController
{
    /**
     * @Route("/view/{id}", name="oro_locale_localization_view", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="oro_locale_localization_view",
     *      type="entity",
     *      class="OroLocaleBundle:Localization",
     *      permission="VIEW"
     * )
     *
     * @param Localization $localization
     * @return array
     */
    public function viewAction(Localization $localization)
    {
        return [
            'entity' => $localization
        ];
    }

    /**
     * @Route("/", name="oro_locale_localization_index")
     * @Template
     * @AclAncestor("oro_locale_localization_view")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'entity_class' => Localization::class
        ];
    }

    /**
     * @Route("/create", name="oro_locale_localization_create")
     * @Template("@OroLocale/Localization/update.html.twig")
     * @Acl(
     *     id="oro_locale_localization_create",
     *     type="entity",
     *     permission="CREATE",
     *     class="OroLocaleBundle:Localization"
     * )
     *
     * @return array|RedirectResponse
     */
    public function createAction(Request $request)
    {
        $response = $this->update(new Localization());

        if ($response instanceof RedirectResponse) {
            $message = $this->get(TranslatorInterface::class)->trans(
                'oro.translation.translation.rebuild_cache_required',
                [
                    '%path%' => $this->generateUrl('oro_translation_translation_index'),
                ]
            );
            $request->getSession()->getFlashBag()->add('warning', $message);
        }

        return $response;
    }

    /**
     * @Route("/update/{id}", name="oro_locale_localization_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *     id="oro_locale_localization_update",
     *     type="entity",
     *     permission="EDIT",
     *     class="OroLocaleBundle:Localization"
     * )
     *
     * @param Localization $localization
     *
     * @return array|RedirectResponse
     */
    public function updateAction(Localization $localization)
    {
        return $this->update($localization);
    }

    /**
     * @param Localization $localization
     * @return array|RedirectResponse
     */
    protected function update(Localization $localization)
    {
        $form = $this->createForm(LocalizationType::class, $localization);

        /** @var $handler UpdateHandler */
        $handler = $this->get(UpdateHandler::class);
        return $handler->update(
            $localization,
            $form,
            $this->get(TranslatorInterface::class)->trans('oro.locale.controller.localization.saved.message')
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                UpdateHandler::class
            ]
        );
    }
}

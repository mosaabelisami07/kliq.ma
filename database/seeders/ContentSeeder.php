<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Project;
use App\Models\Service;
use App\Support\Media;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;

/**
 * Starting categories, services and portfolio projects. Safe to run more than
 * once: existing records (matched by slug) are left as edited in the admin.
 */
class ContentSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedCategories();
        $this->seedServices();
        $this->seedProjects();
        $this->seedBrands();
    }

    /**
     * Client brands from KLIQ's own client wall, with their logos from
     * database/seeders/images/brands. Logos replaced in the dashboard are kept.
     */
    private function seedBrands(): void
    {
        $brands = [
            'machaussure' => 'Machaussure',
            'sassty' => 'Sassty',
            '2m' => '2M',
            'wydad-ac' => 'Wydad AC',
            'inwi' => 'inwi',
            'kappa' => 'Kappa',
            'stellantis' => 'Stellantis',
            'cih-bank' => 'CIH Bank',
        ];

        foreach (array_keys($brands) as $order => $file) {
            $brand = Brand::query()->firstOrCreate(['name' => $brands[$file]], ['sort_order' => $order, 'is_active' => true]);
            $logo = database_path("seeders/images/brands/{$file}.png");

            if (blank($brand->logo) && is_file($logo)) {
                $brand->update(['logo' => Media::disk()->putFile('brands', new File($logo), 'public')]);
            }
        }
    }

    private function seedCategories(): void
    {
        $categories = [
            'campaigns' => ['en' => 'Campaigns', 'fr' => 'Campagnes', 'ar' => 'حملات'],
            'film' => ['en' => 'Film', 'fr' => 'Film', 'ar' => 'أفلام'],
            'photography' => ['en' => 'Photography', 'fr' => 'Photographie', 'ar' => 'تصوير فوتوغرافي'],
            'digital' => ['en' => 'Digital', 'fr' => 'Digital', 'ar' => 'رقمي'],
        ];

        foreach (array_keys($categories) as $order => $slug) {
            Category::query()->firstOrCreate(['slug' => $slug], ['name' => $categories[$slug], 'sort_order' => $order]);
        }
    }

    private function seedServices(): void
    {
        foreach ($this->services() as $order => $service) {
            Service::query()->firstOrCreate(['slug' => $service['slug']], [...$service, 'sort_order' => $order, 'is_active' => true]);
        }
    }

    private function seedProjects(): void
    {
        $categories = Category::query()->pluck('id', 'slug');

        foreach ($this->projects() as $order => $data) {
            $project = Project::query()->firstOrCreate(['slug' => $data['slug']], [
                ...collect($data)->except('category')->all(),
                'category_id' => $categories[$data['category']] ?? null,
                'is_published' => true,
                'is_featured' => true,
                'sort_order' => $order,
            ]);

            $image = database_path("seeders/images/{$project->slug}.jpg");

            if (blank($project->thumbnail) && is_file($image)) {
                $project->update([
                    'thumbnail' => Media::disk()->putFile('projects', new File($image), 'public'),
                ]);
            }

            if ($project->images()->doesntExist()) {
                foreach (glob(database_path("seeders/images/{$project->slug}-[0-9].jpg")) as $index => $galleryImage) {
                    $project->images()->create([
                        'path' => Media::disk()->putFile("projects/{$project->id}", new File($galleryImage), 'public'),
                        'sort_order' => $index,
                    ]);
                }
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function projects(): array
    {
        return [
            [
                'slug' => 'wydad-kappa',
                'category' => 'campaigns',
                'client' => 'Wydad AC × Kappa',
                'title' => ['en' => 'Wydad AC × Kappa', 'fr' => 'Wydad AC × Kappa', 'ar' => 'الوداد الرياضي × Kappa'],
                'summary' => [
                    'en' => 'Jersey launch campaign with a cinematic, streetwear-inspired visual direction.',
                    'fr' => 'Campagne de lancement de maillot à la direction visuelle cinématographique, inspirée du streetwear.',
                    'ar' => 'حملة إطلاق قميص بتوجه بصري سينمائي مستوحى من أسلوب الشارع.',
                ],
                'services_provided' => [
                    'en' => "Campaign\nFilm\nPhotography",
                    'fr' => "Campagne\nFilm\nPhotographie",
                    'ar' => "حملة\nفيلم\nتصوير فوتوغرافي",
                ],
            ],
            [
                'slug' => 'raja-umbro',
                'category' => 'film',
                'client' => 'Raja CA × Umbro',
                'title' => ['en' => 'Raja CA × Umbro', 'fr' => 'Raja CA × Umbro', 'ar' => 'الرجاء الرياضي × Umbro'],
                'summary' => [
                    'en' => 'Football-related creative production and campaign visuals.',
                    'fr' => 'Production créative autour du football et visuels de campagne.',
                    'ar' => 'إنتاج إبداعي مرتبط بكرة القدم ومرئيات الحملة.',
                ],
                'services_provided' => [
                    'en' => "Film\nPhotography",
                    'fr' => "Film\nPhotographie",
                    'ar' => "فيلم\nتصوير فوتوغرافي",
                ],
            ],
            [
                'slug' => 'bun-and-patty',
                'category' => 'digital',
                'client' => 'Bun & Patty',
                'title' => ['en' => 'Bun & Patty', 'fr' => 'Bun & Patty', 'ar' => 'Bun & Patty'],
                'summary' => [
                    'en' => 'From food visuals and launch communication to digital advertising.',
                    'fr' => 'Des visuels culinaires et de la communication de lancement à la publicité digitale.',
                    'ar' => 'من صور الأطباق وتواصل الإطلاق إلى الإعلانات الرقمية.',
                ],
                'services_provided' => [
                    'en' => "Launch\nContent\nDigital advertising",
                    'fr' => "Lancement\nContenu\nPublicité digitale",
                    'ar' => "إطلاق\nمحتوى\nإعلانات رقمية",
                ],
            ],
            [
                'slug' => 'wattsup',
                'category' => 'campaigns',
                'client' => 'WattsUp',
                'title' => ['en' => 'WattsUp', 'fr' => 'WattsUp', 'ar' => 'WattsUp'],
                'summary' => [
                    'en' => 'Product-focused advertising with motion design and digital content.',
                    'fr' => 'Publicité centrée produit avec motion design et contenus digitaux.',
                    'ar' => 'إعلانات تركّز على المنتج مع موشن ديزاين ومحتوى رقمي.',
                ],
                'services_provided' => [
                    'en' => "Commercial\nMotion design\nProduct content",
                    'fr' => "Publicité\nMotion design\nContenu produit",
                    'ar' => "إعلان\nموشن ديزاين\nمحتوى المنتج",
                ],
            ],
            [
                'slug' => 'luigi',
                'category' => 'photography',
                'client' => 'Luigi',
                'title' => ['en' => 'Luigi', 'fr' => 'Luigi', 'ar' => 'Luigi'],
                'summary' => [
                    'en' => 'Premium restaurant imagery and visual storytelling.',
                    'fr' => 'Imagerie haut de gamme pour un restaurant et narration visuelle.',
                    'ar' => 'صور راقية لمطعم وسرد بصري.',
                ],
                'services_provided' => [
                    'en' => "Food photography\nVisual content",
                    'fr' => "Photographie culinaire\nContenu visuel",
                    'ar' => "تصوير الأطباق\nمحتوى بصري",
                ],
            ],
            [
                'slug' => 'musee-automobile',
                'category' => 'film',
                'client' => "Musée de l'Automobile",
                'title' => ['en' => "Musée de l'Automobile", 'fr' => "Musée de l'Automobile", 'ar' => 'متحف السيارات'],
                'summary' => [
                    'en' => 'Museum film showcasing automotive heritage.',
                    'fr' => 'Film de musée mettant en valeur le patrimoine automobile.',
                    'ar' => 'فيلم للمتحف يُبرز تراث السيارات.',
                ],
                'services_provided' => [
                    'en' => "Film\nProduction",
                    'fr' => "Film\nProduction",
                    'ar' => "فيلم\nإنتاج",
                ],
            ],
            [
                'slug' => 'd-cap',
                'category' => 'film',
                'client' => 'D-CAP',
                'video_url' => 'https://youtu.be/tbvsW-nLytA',
                'title' => ['en' => 'D-CAP', 'fr' => 'D-CAP', 'ar' => 'D-CAP'],
                'summary' => [
                    'en' => 'Brand video for D-CAP Maroc featuring Mouna Fettou.',
                    'fr' => 'Vidéo de marque pour D-CAP Maroc avec Mouna Fettou.',
                    'ar' => 'فيديو للعلامة D-CAP المغرب بمشاركة منى فتو.',
                ],
                'services_provided' => [
                    'en' => "Video\nProduction",
                    'fr' => "Vidéo\nProduction",
                    'ar' => "فيديو\nإنتاج",
                ],
            ],
            [
                'slug' => 'blassty',
                'category' => 'film',
                'client' => 'BLASSTY',
                'video_url' => 'https://youtu.be/4QWRu-MpXHw',
                'title' => ['en' => 'BLASSTY', 'fr' => 'BLASSTY', 'ar' => 'BLASSTY'],
                'summary' => [
                    'en' => 'Launch video for BLASSTY, a real-time mobility app for Rabat, Salé and Temara.',
                    'fr' => 'Vidéo de lancement de BLASSTY, une application de mobilité en temps réel pour Rabat, Salé et Témara.',
                    'ar' => 'فيديو إطلاق تطبيق BLASSTY للتنقل في الوقت الحقيقي بالرباط وسلا وتمارة.',
                ],
                'services_provided' => [
                    'en' => "Video\nProduction",
                    'fr' => "Vidéo\nProduction",
                    'ar' => "فيديو\nإنتاج",
                ],
            ],
            [
                'slug' => 'couleurs-berberes',
                'category' => 'film',
                'client' => 'Couleurs Berbères',
                'video_url' => 'https://www.instagram.com/reel/DYLM1UgoxEn/',
                'title' => ['en' => 'Couleurs Berbères', 'fr' => 'Couleurs Berbères', 'ar' => 'Couleurs Berbères'],
                'summary' => [
                    'en' => 'Instagram reel for Couleurs Berbères.',
                    'fr' => 'Reel Instagram pour Couleurs Berbères.',
                    'ar' => 'ريل إنستغرام لـ Couleurs Berbères.',
                ],
                'services_provided' => [
                    'en' => "Reel\nSocial media",
                    'fr' => "Reel\nRéseaux sociaux",
                    'ar' => "ريل\nوسائل التواصل الاجتماعي",
                ],
            ],
            [
                'slug' => 'atiaf-podcast',
                'category' => 'film',
                'client' => 'Atiaf Podcast',
                'video_url' => 'https://youtu.be/W9-zy_kE90Y',
                'title' => ['en' => 'Atiaf Podcast', 'fr' => 'Atiaf Podcast', 'ar' => 'بودكاست أطياف'],
                'summary' => [
                    'en' => 'Video podcast episode for Atiaf Podcast.',
                    'fr' => 'Épisode de podcast vidéo pour Atiaf Podcast.',
                    'ar' => 'حلقة بودكاست مصوّرة لبودكاست أطياف.',
                ],
                'services_provided' => [
                    'en' => "Podcast\nVideo",
                    'fr' => "Podcast\nVidéo",
                    'ar' => "بودكاست\nفيديو",
                ],
            ],
            [
                'slug' => 'inwi',
                'category' => 'film',
                'client' => 'inwi',
                'video_url' => 'https://youtu.be/lOWqlXEBHPQ',
                'title' => ['en' => 'inwi', 'fr' => 'inwi', 'ar' => 'inwi'],
                'summary' => [
                    'en' => 'Twin Center video for inwi.',
                    'fr' => 'Vidéo Twin Center pour inwi.',
                    'ar' => 'فيديو Twin Center لـ inwi.',
                ],
                'services_provided' => [
                    'en' => "Video\nProduction",
                    'fr' => "Vidéo\nProduction",
                    'ar' => "فيديو\nإنتاج",
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function services(): array
    {
        return [
            [
                'slug' => 'social-media-management',
                'icon' => 'share-2',
                'title' => ['en' => 'Social Media Management', 'fr' => 'Gestion des réseaux sociaux', 'ar' => 'إدارة وسائل التواصل الاجتماعي'],
                'summary' => [
                    'en' => 'An always-on presence with a recognisable voice, consistent content and communities that grow.',
                    'fr' => 'Une présence continue, une voix reconnaissable, des contenus cohérents et des communautés qui grandissent.',
                    'ar' => 'حضور دائم بصوت مميز ومحتوى متسق ومجتمعات تنمو.',
                ],
                'body' => [
                    'en' => 'We plan, create and publish content across your social channels, manage your community and report on what works so your presence keeps improving.',
                    'fr' => 'Nous planifions, créons et publions vos contenus sur vos réseaux, animons votre communauté et analysons ce qui fonctionne pour améliorer continuellement votre présence.',
                    'ar' => 'نخطط وننتج وننشر المحتوى عبر قنواتك الاجتماعية، وندير مجتمعك، ونرصد ما ينجح لتتحسن حضورك باستمرار.',
                ],
                'deliverables' => [
                    'en' => "Editorial calendars\nCommunity management\nContent publishing\nMonthly reporting",
                    'fr' => "Calendriers éditoriaux\nGestion de communauté\nPublication des contenus\nRapports mensuels",
                    'ar' => "تقويم المحتوى\nإدارة المجتمع\nنشر المحتوى\nتقارير شهرية",
                ],
            ],
            [
                'slug' => 'digital-marketing-strategy',
                'icon' => 'target',
                'title' => ['en' => 'Digital Marketing Strategy', 'fr' => 'Stratégie de marketing digital', 'ar' => 'استراتيجية التسويق الرقمي'],
                'summary' => [
                    'en' => 'A clear plan that connects your business goals to the right audiences, channels and messages.',
                    'fr' => 'Un plan clair qui relie vos objectifs aux bonnes audiences, aux bons canaux et aux bons messages.',
                    'ar' => 'خطة واضحة تربط أهدافك التجارية بالجمهور والقنوات والرسائل المناسبة.',
                ],
                'body' => [
                    'en' => 'We analyse your market, audience and current presence, then build a practical roadmap with priorities, channels and measurable objectives.',
                    'fr' => 'Nous analysons votre marché, votre audience et votre présence actuelle, puis construisons une feuille de route concrète avec priorités, canaux et objectifs mesurables.',
                    'ar' => 'نحلل سوقك وجمهورك وحضورك الحالي، ثم نبني خارطة طريق عملية بالأولويات والقنوات وأهداف قابلة للقياس.',
                ],
                'deliverables' => [
                    'en' => "Market & audience analysis\nPositioning & messaging\nChannel plan\nKPIs & measurement framework",
                    'fr' => "Analyse du marché et de l’audience\nPositionnement et messages\nPlan de canaux\nKPI et cadre de mesure",
                    'ar' => "تحليل السوق والجمهور\nالتموضع والرسائل\nخطة القنوات\nمؤشرات الأداء وإطار القياس",
                ],
            ],
            [
                'slug' => 'branding-visual-identity',
                'icon' => 'palette',
                'title' => ['en' => 'Branding & Visual Identity', 'fr' => 'Branding & identité visuelle', 'ar' => 'العلامة التجارية والهوية البصرية'],
                'summary' => [
                    'en' => 'Distinctive brands built from strategy up — logos, visual systems and guidelines that scale.',
                    'fr' => 'Des marques distinctives construites à partir de la stratégie — logos, systèmes visuels et chartes évolutives.',
                    'ar' => 'علامات تجارية مميزة تُبنى انطلاقًا من الاستراتيجية — شعارات وأنظمة بصرية وأدلة قابلة للتوسع.',
                ],
                'body' => [
                    'en' => 'We define what your brand stands for and translate it into a coherent identity that works across every touchpoint, from social media to print.',
                    'fr' => 'Nous définissons ce que votre marque représente et le traduisons en une identité cohérente sur tous les points de contact, des réseaux sociaux à l’imprimé.',
                    'ar' => 'نحدد ما تمثله علامتك التجارية ونترجمه إلى هوية متماسكة تعمل في كل نقاط التواصل، من وسائل التواصل إلى المطبوعات.',
                ],
                'deliverables' => [
                    'en' => "Brand strategy\nLogo design\nVisual identity system\nBrand guidelines",
                    'fr' => "Stratégie de marque\nCréation de logo\nSystème d’identité visuelle\nCharte graphique",
                    'ar' => "استراتيجية العلامة\nتصميم الشعار\nنظام الهوية البصرية\nدليل العلامة",
                ],
            ],
            [
                'slug' => 'graphic-design',
                'icon' => 'pen-tool',
                'title' => ['en' => 'Graphic Design & Creative Content', 'fr' => 'Design graphique & contenus créatifs', 'ar' => 'التصميم الجرافيكي والمحتوى الإبداعي'],
                'summary' => [
                    'en' => 'Visuals that stop the scroll — social designs, campaign key visuals, print and presentations.',
                    'fr' => 'Des visuels qui arrêtent le scroll — designs social media, key visuals, print et présentations.',
                    'ar' => 'تصاميم توقف التمرير — تصاميم للشبكات الاجتماعية ومرئيات الحملات والمطبوعات والعروض.',
                ],
                'body' => [
                    'en' => 'Our designers create on-brand visuals for every format your business needs, keeping quality and consistency high across channels.',
                    'fr' => 'Nos designers créent des visuels fidèles à votre marque pour tous les formats dont vous avez besoin, avec une qualité et une cohérence constantes.',
                    'ar' => 'يصمم فريقنا مرئيات متوافقة مع علامتك لكل الصيغ التي يحتاجها نشاطك، مع الحفاظ على الجودة والاتساق عبر القنوات.',
                ],
                'deliverables' => [
                    'en' => "Social media designs\nCampaign key visuals\nPrint & packaging\nPresentations",
                    'fr' => "Designs réseaux sociaux\nKey visuals de campagne\nPrint & packaging\nPrésentations",
                    'ar' => "تصاميم الشبكات الاجتماعية\nمرئيات الحملات\nالمطبوعات والتغليف\nالعروض التقديمية",
                ],
            ],
            [
                'slug' => 'content-creation',
                'icon' => 'camera',
                'title' => ['en' => 'Content Creation', 'fr' => 'Création de contenu', 'ar' => 'صناعة المحتوى'],
                'summary' => [
                    'en' => 'Photography, copy and short-form content made for the feed — native, engaging and on-brand.',
                    'fr' => 'Photo, rédaction et formats courts pensés pour les réseaux — natifs, engageants et fidèles à la marque.',
                    'ar' => 'تصوير وكتابة ومحتوى قصير صُنع للمنصات — طبيعي وجذاب ومتوافق مع العلامة.',
                ],
                'body' => [
                    'en' => 'From product shoots to captions, we produce the content your channels need to stay active, relevant and recognisable.',
                    'fr' => 'Des shootings produits aux légendes, nous produisons les contenus dont vos canaux ont besoin pour rester actifs, pertinents et reconnaissables.',
                    'ar' => 'من جلسات تصوير المنتجات إلى النصوص المرافقة، ننتج المحتوى الذي تحتاجه قنواتك لتبقى نشطة وذات صلة ومميزة.',
                ],
                'deliverables' => [
                    'en' => "Photo shoots\nCopywriting\nReels & short-form video\nCreator content",
                    'fr' => "Shootings photo\nRédaction\nReels & vidéos courtes\nContenus créateurs",
                    'ar' => "جلسات تصوير\nكتابة المحتوى\nريلز وفيديوهات قصيرة\nمحتوى صناع المحتوى",
                ],
            ],
            [
                'slug' => 'paid-advertising',
                'icon' => 'megaphone',
                'title' => ['en' => 'Paid Advertising Campaigns', 'fr' => 'Campagnes publicitaires payantes', 'ar' => 'الحملات الإعلانية المدفوعة'],
                'summary' => [
                    'en' => 'Targeted campaigns on social and search platforms, built around clear objectives and budgets.',
                    'fr' => 'Des campagnes ciblées sur les réseaux sociaux et moteurs de recherche, construites autour d’objectifs et de budgets clairs.',
                    'ar' => 'حملات موجّهة على منصات التواصل ومحركات البحث، مبنية حول أهداف وميزانيات واضحة.',
                ],
                'body' => [
                    'en' => 'We set up, run and optimise paid campaigns, test creatives and audiences, and report transparently on spend and results.',
                    'fr' => 'Nous configurons, pilotons et optimisons vos campagnes, testons créations et audiences, et rendons compte en toute transparence des dépenses et résultats.',
                    'ar' => 'نُعِدّ الحملات المدفوعة وندير تشغيلها ونحسّنها، ونختبر التصاميم والجماهير، ونقدم تقارير شفافة عن الإنفاق والنتائج.',
                ],
                'deliverables' => [
                    'en' => "Campaign setup\nAudience targeting\nCreative testing\nPerformance reporting",
                    'fr' => "Mise en place des campagnes\nCiblage des audiences\nTests créatifs\nRapports de performance",
                    'ar' => "إعداد الحملات\nاستهداف الجمهور\nاختبار التصاميم\nتقارير الأداء",
                ],
            ],
            [
                'slug' => 'video-production',
                'icon' => 'clapperboard',
                'title' => ['en' => 'Video Production', 'fr' => 'Production vidéo', 'ar' => 'الإنتاج المرئي'],
                'summary' => [
                    'en' => 'Brand films, commercials, reels and motion design — from script to final edit.',
                    'fr' => 'Films de marque, publicités, reels et motion design — du script au montage final.',
                    'ar' => 'أفلام العلامات التجارية والإعلانات والريلز والموشن ديزاين — من السيناريو إلى المونتاج النهائي.',
                ],
                'body' => [
                    'en' => 'We handle concept, production and post-production to deliver video content that tells your story and performs on every screen.',
                    'fr' => 'Nous gérons le concept, le tournage et la post-production pour livrer des vidéos qui racontent votre histoire et performent sur tous les écrans.',
                    'ar' => 'نتولى الفكرة والتصوير وما بعد الإنتاج لنقدم محتوى مرئيًا يروي قصتك ويحقق الأداء على كل الشاشات.',
                ],
                'deliverables' => [
                    'en' => "Brand films & commercials\nReels & short-form\nMotion design\nPost-production",
                    'fr' => "Films de marque & publicités\nReels & formats courts\nMotion design\nPost-production",
                    'ar' => "أفلام العلامة والإعلانات\nريلز ومحتوى قصير\nموشن ديزاين\nما بعد الإنتاج",
                ],
            ],
            [
                'slug' => 'seo-online-visibility',
                'icon' => 'search',
                'title' => ['en' => 'SEO & Online Visibility', 'fr' => 'SEO & visibilité en ligne', 'ar' => 'تحسين محركات البحث والظهور الرقمي'],
                'summary' => [
                    'en' => 'Help the right people find you on search engines and maps, with content and technical foundations that last.',
                    'fr' => 'Aidez les bonnes personnes à vous trouver sur les moteurs de recherche et les cartes, avec des bases techniques et éditoriales durables.',
                    'ar' => 'ساعد الأشخاص المناسبين على إيجادك في محركات البحث والخرائط، بأسس تقنية ومحتوى مستدام.',
                ],
                'body' => [
                    'en' => 'We audit your site, fix technical issues, improve on-page content and optimise your local listings to grow organic visibility over time.',
                    'fr' => 'Nous auditons votre site, corrigeons les problèmes techniques, améliorons les contenus et optimisons vos fiches locales pour développer votre visibilité naturelle.',
                    'ar' => 'نراجع موقعك ونعالج المشكلات التقنية ونحسّن المحتوى ونطوّر ظهورك في القوائم المحلية لتنمية حضورك الطبيعي مع الوقت.',
                ],
                'deliverables' => [
                    'en' => "SEO audit\nOn-page optimisation\nLocal SEO & Google Business Profile\nKeyword & content plan",
                    'fr' => "Audit SEO\nOptimisation on-page\nSEO local & fiche Google\nPlan de mots-clés et contenus",
                    'ar' => "تدقيق SEO\nتحسين الصفحات\nSEO المحلي وملف Google التجاري\nخطة الكلمات المفتاحية والمحتوى",
                ],
            ],
        ];
    }
}

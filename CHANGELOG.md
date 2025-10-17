# Changelog

## [Unreleased]

### Features

* **integrations:** add SEOmatic integration for pushing events to Google Tag Manager data layer
  - New modular integration architecture at `/src/integrations/`
  - SEOmatic plugin detection and status checking
  - Push Smart Links click events to GTM/Google Analytics via SEOmatic
  - Configurable event types (redirect, button_click, qr_scan)
  - Customizable event prefix for GTM event names
  - Comprehensive event data including device, platform, geographic, and source tracking
  - Settings UI in Analytics page for easy configuration
  - Zero performance impact when disabled or SEOmatic not installed
  - Fully documented with README section and GTM trigger examples

## [1.22.1](https://github.com/LindemannRock/craft-smart-links/compare/v1.22.0...v1.22.1) (2025-10-16)


### Bug Fixes

* update installation instructions for Composer and DDEV ([e544109](https://github.com/LindemannRock/craft-smart-links/commit/e5441096ee51895df0d91dc19e2b194e73cac03f))

## [1.22.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.21.0...v1.22.0) (2025-10-16)


### Features

* **dependencies:** add lindemannrock/craft-logging-library as a requirement ([93338df](https://github.com/LindemannRock/craft-smart-links/commit/93338df51294a2ddd23a11b81ff01728e49a0183))

## [1.21.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.20.0...v1.21.0) (2025-10-16)


### Features

* **logging:** add detailed logging configuration and documentation ([be6f11a](https://github.com/LindemannRock/craft-smart-links/commit/be6f11a18ab8705578119abebef941158711a327))

## [1.20.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.19.4...v1.20.0) (2025-10-16)


### Features

* integrate LindemannRock Logging Library with structured PSR-3 logging across all controllers and services ([3cd09c5](https://github.com/LindemannRock/craft-smart-links/commit/3cd09c59e752ddd052740c968c14a008d90117f5))

## [1.19.4](https://github.com/LindemannRock/craft-smart-links/compare/v1.19.3...v1.19.4) (2025-10-02)


### Bug Fixes

* remove random salt from IP hashing to accurately count unique visitors ([02f1c8b](https://github.com/LindemannRock/craft-smart-links/commit/02f1c8b80f8cf23eec3fe25200ee50b0d8a341ec))

## [1.19.3](https://github.com/LindemannRock/craft-smart-links/compare/v1.19.2...v1.19.3) (2025-10-02)


### Bug Fixes

* remove clicks column references and resolve duplicate analytics entries ([78b933a](https://github.com/LindemannRock/craft-smart-links/commit/78b933a2cae7c78bb30e1697e4e61d6823c09600))

## [1.19.2](https://github.com/LindemannRock/craft-smart-links/compare/v1.19.1...v1.19.2) (2025-10-02)


### Bug Fixes

* handle NULL and incorrect platform values in analytics chart and cleanup ([4cf21be](https://github.com/LindemannRock/craft-smart-links/commit/4cf21be8971d5ba7f010fc96cd708cbb97729ad3))

## [1.19.1](https://github.com/LindemannRock/craft-smart-links/compare/v1.19.0...v1.19.1) (2025-10-02)


### Features

* add checkbox group for enabling Smart Links on specific sites ([a0d6f85](https://github.com/LindemannRock/craft-smart-links/commit/a0d6f8586d7135625128b61857bc50d52abcd46d))
* add configurable URL prefixes for smart links and QR codes ([f7239b2](https://github.com/LindemannRock/craft-smart-links/commit/f7239b2f47d3e3329c1d0bc4dc181e69eb033b4d))
* add CSRF token refresh for cached pages and fix metadata serialization ([c22c2b1](https://github.com/LindemannRock/craft-smart-links/commit/c22c2b138e3c93382621cb7f1fcaaf9999a4c898))
* add custom QR code template settings and update related translations ([c362642](https://github.com/LindemannRock/craft-smart-links/commit/c362642eb71a064e27da7cbc360225efe100ae3e))
* add customizable URL prefixes and templates for smart links and QR codes ([eff264d](https://github.com/LindemannRock/craft-smart-links/commit/eff264d7cc39d6d81d622f1628978a6d261ef28f))
* add enabledSites property to Settings model for site-specific Smart Links configuration ([828b105](https://github.com/LindemannRock/craft-smart-links/commit/828b105f4fc2335edec9227be4b0a81198233e31))
* Add Field Layout support to Smart Links element type ([7b77015](https://github.com/LindemannRock/craft-smart-links/commit/7b77015311250dd08af76b7069c8bb3e0d8377eb))
* Add field layout support with project config sync ([21e0ba8](https://github.com/LindemannRock/craft-smart-links/commit/21e0ba8551bd5a7c58e603e93db3651cadcac3cc))
* Add interaction type breakdown to Performance card ([9c47423](https://github.com/LindemannRock/craft-smart-links/commit/9c47423dc73360e24710ff79fcf001badf0d5de9))
* add multi-site management and site selection configuration for Smart Links ([304ebc1](https://github.com/LindemannRock/craft-smart-links/commit/304ebc1470760ad2e8e7f66d11996358bc81f279))
* add plugin credit component to settings and analytics templates ([c22cf96](https://github.com/LindemannRock/craft-smart-links/commit/c22cf96fde791c79b1e650964985cf44f8beeba6))
* add QR code cache busting setting to fix tracking with CDN caching ([72eac94](https://github.com/LindemannRock/craft-smart-links/commit/72eac947123e427262617346103543810347fb4d))
* Add read-only mode for Smart Links settings when allowAdminChanges is disabled ([a9ad703](https://github.com/LindemannRock/craft-smart-links/commit/a9ad70344ceaf8b304b848de739600a2d0d00e90))
* add site settings and default settings row to smartlinks_settings table ([c143d41](https://github.com/LindemannRock/craft-smart-links/commit/c143d41a1fb7d5b2cd0c2d8deb254284a5bff4e2))
* add Smart Links utility template with link statistics and recent analytics ([acf62c7](https://github.com/LindemannRock/craft-smart-links/commit/acf62c7ad344275381fdce7cfbefa74b8f674591))
* enhance CSRF token response with device detection information ([5af440b](https://github.com/LindemannRock/craft-smart-links/commit/5af440ba912c25b3e97df877cf8f60de1747af26))
* enhance README with additional features for image management and landing page customization ([8162b36](https://github.com/LindemannRock/craft-smart-links/commit/8162b36ffec42db3b9701d2cf6dd96cf92f9617f))
* enhance settings handling with additional debug logging and auto-setting for qrLogoVolumeUid ([a3b7d71](https://github.com/LindemannRock/craft-smart-links/commit/a3b7d7112493de5c0c56a27c29914bf02c87768a))
* enhance settings UI with URL and template configuration options for smart links and QR codes ([239219d](https://github.com/LindemannRock/craft-smart-links/commit/239219d1c4449067f558148b5bab2d1ca0ae7d88))
* implement site-specific Smart Links functionality and enable site selection in templates ([6c87105](https://github.com/LindemannRock/craft-smart-links/commit/6c871052fcfa89f39611b97ed62c4bd2d1a04d60))
* Improve analytics data management and platform display ([d60def7](https://github.com/LindemannRock/craft-smart-links/commit/d60def7a5fa4014e6ed9a201e251bedc28879c4f))
* initial Smart Links plugin implementation ([6b5c0ed](https://github.com/LindemannRock/craft-smart-links/commit/6b5c0ed5911f8ecdb803cb0c76395fdce7bb03ef))
* refactor analytics tracking to client-side JavaScript for CDN compatibility ([edfd7a9](https://github.com/LindemannRock/craft-smart-links/commit/edfd7a91bccb7bacc0caeba9ea805e59c2b3cf42))
* Register project config event handlers and save field layout UID ([3490026](https://github.com/LindemannRock/craft-smart-links/commit/34900265fa6609fd8fbc092d67fa53100dab01dc))
* remove redundant enabled and clicks columns from smartlinks table ([ec79d43](https://github.com/LindemannRock/craft-smart-links/commit/ec79d43e4a0b28e4415150a3d6297cdbbe4c069e))
* update caching strategy in RedirectController to vary by device type ([9bb8e4b](https://github.com/LindemannRock/craft-smart-links/commit/9bb8e4bd881509e72fb5f8f60f2c8d9726ddfbc1))
* update README and migration for site settings in Smart Links ([c309b1b](https://github.com/LindemannRock/craft-smart-links/commit/c309b1b98e1a00b75f09c039b6054c736e0ed1b5))


### Bug Fixes

* enabled status requiring two saves to work ([19a7723](https://github.com/LindemannRock/craft-smart-links/commit/19a77233e4ce6add702e903c453217b4d0392fd5))
* enabled status requiring two saves to work ([1106a02](https://github.com/LindemannRock/craft-smart-links/commit/1106a028603c6c0886800ad78cc0822ba66f3b2f))
* force new release for enabled status fix ([202e1fd](https://github.com/LindemannRock/craft-smart-links/commit/202e1fdb4e15ad96dd56ad261ec57c0b13ff9c17))
* handle empty QR logo and image IDs in SmartLinksController ([d9a7e65](https://github.com/LindemannRock/craft-smart-links/commit/d9a7e65055ca27534f382ad29aec7a95eeaa10e7))
* improve description in CleanupAnalyticsJob and format .gitignore entries ([3a58cbc](https://github.com/LindemannRock/craft-smart-links/commit/3a58cbc9cd5403b2413e9a644ec7b7026baab72f))
* improve tracking and analytics display ([d94701c](https://github.com/LindemannRock/craft-smart-links/commit/d94701c5290c2323bc811a7b1acdf0fd5a8a6f48))
* make redirects truly cache-safe by moving URL selection to client-side ([bdbfa15](https://github.com/LindemannRock/craft-smart-links/commit/bdbfa15bacdaf5484602b10e623f935420c509d9))
* multi-site analytics tracking ([493bbc4](https://github.com/LindemannRock/craft-smart-links/commit/493bbc427bca5b0ba4e4575f88c4d98ef1405ac9))
* Preserve QR source parameter and display destination URLs in analytics ([a579481](https://github.com/LindemannRock/craft-smart-links/commit/a579481cb2fc65772a47fc380405b8c106527f78))
* remove development backups and IDE files ([f078fdb](https://github.com/LindemannRock/craft-smart-links/commit/f078fdb024b40398b2ad93c9d9499ffc9172a021))
* replace sendBeacon with fetch POST for CDN compatibility ([71a62dd](https://github.com/LindemannRock/craft-smart-links/commit/71a62dd917f1a1d8ec8d4b9bf97b8ac11708af59))
* Show read-only notice only on Field Layout settings page ([049d7ca](https://github.com/LindemannRock/craft-smart-links/commit/049d7ca021d451ccf7756a1e06af4c4b73949924))
* smart link tracking to work with static page caching ([1fb2774](https://github.com/LindemannRock/craft-smart-links/commit/1fb2774df9761bd8b8c5c7aaad9cf925ed969add))
* Smart Links database schema to match working installation ([03fe1dd](https://github.com/LindemannRock/craft-smart-links/commit/03fe1dd45e8985bafe8996f3b38dde2d01740057))
* trigger release for enabled status fix ([3daded7](https://github.com/LindemannRock/craft-smart-links/commit/3daded757027584bfc2a855fddd6e88f239a650a))
* update copyright notice in LICENSE file ([3a2531c](https://github.com/LindemannRock/craft-smart-links/commit/3a2531cd2086d5dddc2e7a16905ed3ae6fa35f05))
* update device detection method in RedirectController ([198fc1a](https://github.com/LindemannRock/craft-smart-links/commit/198fc1acadd5a050052b2c1ca8db9343bfea914e))
* update device detection method in RedirectController ([3e7fb1a](https://github.com/LindemannRock/craft-smart-links/commit/3e7fb1abcfd76bbbbecd9fb4bfca2706edbf47c9))
* update displayName method to return plugin name and rename iconPath to icon ([aca60a0](https://github.com/LindemannRock/craft-smart-links/commit/aca60a06bc689820a2d407270541e1c4222d5853))
* update instruction for custom redirect template field ([de0a299](https://github.com/LindemannRock/craft-smart-links/commit/de0a299fd959ff56f2f8a48357e0e3424455548f))
* update PHP requirement from ^8.0.2 to ^8.2 in composer.json ([29d375d](https://github.com/LindemannRock/craft-smart-links/commit/29d375d857f2f3eb9277318c24150ac3034e1120))
* update repository links in README and composer.json to reflect new naming ([a239296](https://github.com/LindemannRock/craft-smart-links/commit/a239296fbe4e9cc70bd86863bd89fbcec3031043))
* update requirements in README for clarity and consistency ([a17ca25](https://github.com/LindemannRock/craft-smart-links/commit/a17ca2501f162c2c60df0f82449f142f5337d7e3))
* update site selection logic in multi-site configuration ([d2bd97b](https://github.com/LindemannRock/craft-smart-links/commit/d2bd97baae4bd6f865f9da53621e581f12e36cca))
* Update URL assignment to check both redirectUrl and buttonUrl formats ([832f196](https://github.com/LindemannRock/craft-smart-links/commit/832f1962242a6be8ac206f21e5d27ea8b1f212bd))
* use action URLs for tracking endpoints to bypass CDN caching ([67fb674](https://github.com/LindemannRock/craft-smart-links/commit/67fb674273cd8649e817fb45a20ba7d4e765bac4))
* use action URLs for tracking endpoints to bypass CDN caching ([44ba917](https://github.com/LindemannRock/craft-smart-links/commit/44ba917e05622ac04902e6ac4426bccbf675e207))
* use array_key_exists for attribute checks in settings configuration ([31e8b40](https://github.com/LindemannRock/craft-smart-links/commit/31e8b40191b9c7f1d689e86e97a10f26f401a347))
* wait for tracking to complete before redirect ([4400b5e](https://github.com/LindemannRock/craft-smart-links/commit/4400b5e7196541cb65ceaa86b40bbc570594be60))


### Miscellaneous Chores

* **main:** release 1.0.1 ([294ae46](https://github.com/LindemannRock/craft-smart-links/commit/294ae468ee6b64da59a31f57a0e0f572c6ced2f3))
* **main:** release 1.0.1 ([9299d1f](https://github.com/LindemannRock/craft-smart-links/commit/9299d1f2373367d9de5c484e1874c6f4d3a77076))
* **main:** release 1.0.2 ([7698cc1](https://github.com/LindemannRock/craft-smart-links/commit/7698cc1f5db443f55e204739cf02145a04d5c56e))
* **main:** release 1.0.2 ([44a53cb](https://github.com/LindemannRock/craft-smart-links/commit/44a53cbe9fc5e56b938294837e08ef425816faa4))
* **main:** release 1.0.3 ([84b001d](https://github.com/LindemannRock/craft-smart-links/commit/84b001df4b9e3286e6e054a6a879cd7c9cd6c0b4))
* **main:** release 1.0.3 ([e9bb3d7](https://github.com/LindemannRock/craft-smart-links/commit/e9bb3d7c7e255cacb98034bc13bdb4b5bf59df06))
* **main:** release 1.0.4 ([4f9d3d4](https://github.com/LindemannRock/craft-smart-links/commit/4f9d3d4f8bb94c35e0977b2db867014887b974ed))
* **main:** release 1.0.4 ([4152201](https://github.com/LindemannRock/craft-smart-links/commit/415220154477e18f4c3520099f56e2f1896fc0ff))
* **main:** release 1.1.0 ([36ec264](https://github.com/LindemannRock/craft-smart-links/commit/36ec26487148541cdd81f3f7fbe5209ff8864200))
* **main:** release 1.1.0 ([907bfc8](https://github.com/LindemannRock/craft-smart-links/commit/907bfc8df2257952af0871df00978895a8075f2e))
* **main:** release 1.10.0 ([48b4cb4](https://github.com/LindemannRock/craft-smart-links/commit/48b4cb498e4ad66c568b4a0882f4c2ec997e8e82))
* **main:** release 1.10.0 ([7fd4dab](https://github.com/LindemannRock/craft-smart-links/commit/7fd4dab5128b9b1eff869a483adc4a103ed7f718))
* **main:** release 1.11.0 ([4233d87](https://github.com/LindemannRock/craft-smart-links/commit/4233d87e96e02cd4d37c64a7db0ee3dee8a4da28))
* **main:** release 1.11.0 ([3323a4c](https://github.com/LindemannRock/craft-smart-links/commit/3323a4c57f77e038edae7b2bfd221931c3d99df7))
* **main:** release 1.12.0 ([0d75f44](https://github.com/LindemannRock/craft-smart-links/commit/0d75f44e89b7a3948193d6aab9b178368624b1d2))
* **main:** release 1.12.0 ([49108e6](https://github.com/LindemannRock/craft-smart-links/commit/49108e6c53cf9500c7d031fd0a5321634307517a))
* **main:** release 1.13.0 ([1f3fa72](https://github.com/LindemannRock/craft-smart-links/commit/1f3fa72591d48d84f49490d3024aff0bf1f12036))
* **main:** release 1.13.0 ([4eeff48](https://github.com/LindemannRock/craft-smart-links/commit/4eeff48d0aab2fa264c231833b49f4d8b2a4d503))
* **main:** release 1.13.1 ([0da4182](https://github.com/LindemannRock/craft-smart-links/commit/0da4182d892a93c1160dd3f8d35eb6de9d9cb28c))
* **main:** release 1.13.1 ([2d41b6d](https://github.com/LindemannRock/craft-smart-links/commit/2d41b6d24d404381ce6ab71fcb116127ac04a9d2))
* **main:** release 1.13.2 ([e124691](https://github.com/LindemannRock/craft-smart-links/commit/e12469170ae5d01928ac00c1b075580a76889e66))
* **main:** release 1.13.2 ([edbeb22](https://github.com/LindemannRock/craft-smart-links/commit/edbeb22eee9661710a7f02e173980dbce31c2261))
* **main:** release 1.13.3 ([aa3f90b](https://github.com/LindemannRock/craft-smart-links/commit/aa3f90b235292f3173b516a0b3e7d06f21052df1))
* **main:** release 1.13.3 ([29974bd](https://github.com/LindemannRock/craft-smart-links/commit/29974bd29bf2abff9d02be8e529ec433100f2222))
* **main:** release 1.13.4 ([bb5e398](https://github.com/LindemannRock/craft-smart-links/commit/bb5e398ebb09b46952f8070b267604fd67c1a116))
* **main:** release 1.13.4 ([6e2dfb6](https://github.com/LindemannRock/craft-smart-links/commit/6e2dfb69148f756065f1c6fbaaaeee3d7dc948c0))
* **main:** release 1.13.5 ([a966b73](https://github.com/LindemannRock/craft-smart-links/commit/a966b7340446430f43b2b999afab67ab7f35c0a2))
* **main:** release 1.13.5 ([9efea73](https://github.com/LindemannRock/craft-smart-links/commit/9efea733d4b0009e7f08983d8866465247bed9cd))
* **main:** release 1.13.6 ([d9b2dc1](https://github.com/LindemannRock/craft-smart-links/commit/d9b2dc151d30ee7ba80cf61106247092913438a5))
* **main:** release 1.13.6 ([3fe3b4d](https://github.com/LindemannRock/craft-smart-links/commit/3fe3b4daf4261aa89da31b944c1b15172320a915))
* **main:** release 1.13.7 ([b910c04](https://github.com/LindemannRock/craft-smart-links/commit/b910c041379e49ac4766402e274686e5be48cd0e))
* **main:** release 1.13.7 ([a5b2056](https://github.com/LindemannRock/craft-smart-links/commit/a5b2056e630d374209c259f2740819a013df2f6e))
* **main:** release 1.14.0 ([2991ea6](https://github.com/LindemannRock/craft-smart-links/commit/2991ea65bfe523625c6f85b5068cf1a28c7a6bf8))
* **main:** release 1.14.0 ([589895a](https://github.com/LindemannRock/craft-smart-links/commit/589895a107e355f716706c1c44d86a2eada6b8e9))
* **main:** release 1.15.0 ([74a6b32](https://github.com/LindemannRock/craft-smart-links/commit/74a6b32dddb535f1f30e5d4ffe07471411154a2d))
* **main:** release 1.15.0 ([54507de](https://github.com/LindemannRock/craft-smart-links/commit/54507de8b39d633624fb456451b2a3d381984cc6))
* **main:** release 1.16.0 ([1c924d1](https://github.com/LindemannRock/craft-smart-links/commit/1c924d19e238129c0aed561a3d70fc7996cc3bd5))
* **main:** release 1.16.0 ([eac4aff](https://github.com/LindemannRock/craft-smart-links/commit/eac4affe3c196b79afe57e439b4f9bfeaa2e49e3))
* **main:** release 1.17.0 ([324aa36](https://github.com/LindemannRock/craft-smart-links/commit/324aa36d79f49664e7ea3d3a75d5508bd21ca2d9))
* **main:** release 1.17.0 ([ddde5ac](https://github.com/LindemannRock/craft-smart-links/commit/ddde5ace8e7020aa85ae255c157941726b4a7153))
* **main:** release 1.17.1 ([67a60e3](https://github.com/LindemannRock/craft-smart-links/commit/67a60e373ac619bad24e06c2e78a1e373112f14e))
* **main:** release 1.17.1 ([dd39b37](https://github.com/LindemannRock/craft-smart-links/commit/dd39b374c2b34c0c162136228a32d75a4321d0be))
* **main:** release 1.17.2 ([ccb27f5](https://github.com/LindemannRock/craft-smart-links/commit/ccb27f5e5cfe53ad968a8d439ece24812032db57))
* **main:** release 1.17.2 ([5ad99b1](https://github.com/LindemannRock/craft-smart-links/commit/5ad99b1fae773de22607b4a70ac347481da98cc6))
* **main:** release 1.18.0 ([26e14d3](https://github.com/LindemannRock/craft-smart-links/commit/26e14d39ed9442ddf9501d4f9aca175cf43cdf76))
* **main:** release 1.18.0 ([a26a6ba](https://github.com/LindemannRock/craft-smart-links/commit/a26a6baef33dd7dfe88eb5426d8f5cf2ac0cf39d))
* **main:** release 1.19.0 ([f48d24a](https://github.com/LindemannRock/craft-smart-links/commit/f48d24a9dfb9f2df59441c348c438b4e8d755340))
* **main:** release 1.19.0 ([d740cc7](https://github.com/LindemannRock/craft-smart-links/commit/d740cc78e5821fb9a3a4ab0cfb79458e67388890))
* **main:** release 1.2.0 ([5ab969b](https://github.com/LindemannRock/craft-smart-links/commit/5ab969b09a24f9ed2d534211227465eaba12536a))
* **main:** release 1.2.0 ([9a71da0](https://github.com/LindemannRock/craft-smart-links/commit/9a71da0b3f1079a5421ce36b2282efa1c7a959c3))
* **main:** release 1.2.1 ([160cab5](https://github.com/LindemannRock/craft-smart-links/commit/160cab500248ca727a8bf5df52eeafed4fb23858))
* **main:** release 1.2.1 ([8c08a56](https://github.com/LindemannRock/craft-smart-links/commit/8c08a565b6d43190490d79be65ba69c8ba030bc2))
* **main:** release 1.2.2 ([665d1bf](https://github.com/LindemannRock/craft-smart-links/commit/665d1bf7efec85cba32fdf9efec9e7c3fcd1df7e))
* **main:** release 1.2.2 ([bc6edd6](https://github.com/LindemannRock/craft-smart-links/commit/bc6edd6b93ed78c9002791c5041dd84f1ec8339b))
* **main:** release 1.3.0 ([05f7bd0](https://github.com/LindemannRock/craft-smart-links/commit/05f7bd0346b2d86db88aa592f972b055e74a0401))
* **main:** release 1.3.0 ([c2e627f](https://github.com/LindemannRock/craft-smart-links/commit/c2e627fe2729fec4afcd2626721c810ff2362844))
* **main:** release 1.4.0 ([5c6aaad](https://github.com/LindemannRock/craft-smart-links/commit/5c6aaad5a83c2e3e2ea0e2340a306782d9ed1039))
* **main:** release 1.4.0 ([faafc82](https://github.com/LindemannRock/craft-smart-links/commit/faafc82f62bcf989ff35f19c4ab338ca646f266d))
* **main:** release 1.4.1 ([c3aacb9](https://github.com/LindemannRock/craft-smart-links/commit/c3aacb94caed580a8debd0f9c3446f40ee8f20c6))
* **main:** release 1.4.1 ([c46249d](https://github.com/LindemannRock/craft-smart-links/commit/c46249dd34971b47900f18ddb917f878d8d0496d))
* **main:** release 1.4.2 ([1fa1a6a](https://github.com/LindemannRock/craft-smart-links/commit/1fa1a6a90c2a97f1ebf329495f62cb86a143b4f0))
* **main:** release 1.4.2 ([3091864](https://github.com/LindemannRock/craft-smart-links/commit/3091864ed0c199c18e8b6ef4bdcf4d992f885438))
* **main:** release 1.5.0 ([c9951aa](https://github.com/LindemannRock/craft-smart-links/commit/c9951aa43bcae7a3ff5fec52df712e4a547a8b8c))
* **main:** release 1.5.0 ([2b877b6](https://github.com/LindemannRock/craft-smart-links/commit/2b877b61036631af2e9231c22bd96833a0e72120))
* **main:** release 1.6.0 ([e43f2bf](https://github.com/LindemannRock/craft-smart-links/commit/e43f2bffb4e48e051c61e66169cedb9d5d548b19))
* **main:** release 1.6.0 ([10a835d](https://github.com/LindemannRock/craft-smart-links/commit/10a835d22e014c0e5c9364464389eda34e180657))
* **main:** release 1.7.0 ([a31f28d](https://github.com/LindemannRock/craft-smart-links/commit/a31f28dbe85a38df924556a6a9be5065dc3646ad))
* **main:** release 1.7.0 ([5978949](https://github.com/LindemannRock/craft-smart-links/commit/5978949f053a6e5fc7accc9aae96771481eeb9d7))
* **main:** release 1.7.1 ([0bc5e61](https://github.com/LindemannRock/craft-smart-links/commit/0bc5e61a9739fa19acdb377518cafd1f0260d7c1))
* **main:** release 1.7.1 ([6e3b38e](https://github.com/LindemannRock/craft-smart-links/commit/6e3b38e3fb67195ff597110772b31a3e9534a457))
* **main:** release 1.8.0 ([af64789](https://github.com/LindemannRock/craft-smart-links/commit/af647893ae30445d04b423b81d6d057e5e8fd612))
* **main:** release 1.8.0 ([ab8c894](https://github.com/LindemannRock/craft-smart-links/commit/ab8c894241ede35c84ca061c1b48aec9568f682e))
* **main:** release 1.9.0 ([49279a9](https://github.com/LindemannRock/craft-smart-links/commit/49279a92b0ba86820adcbadb014d913f90d8872a))
* **main:** release 1.9.0 ([9b0b7cc](https://github.com/LindemannRock/craft-smart-links/commit/9b0b7cc1216c8558838237c1d82eb74d34dbfd23))
* **main:** release 1.9.1 ([daf8f8b](https://github.com/LindemannRock/craft-smart-links/commit/daf8f8b885e943f6ca8f35b805d017dd2e7ef51a))
* **main:** release 1.9.1 ([47d7b9f](https://github.com/LindemannRock/craft-smart-links/commit/47d7b9f540d10d75d3d703a7b19bafef7f4720df))
* **main:** release 1.9.2 ([33331fe](https://github.com/LindemannRock/craft-smart-links/commit/33331feda5b84294e28e30ec9c6a1876fa05aa0a))
* **main:** release 1.9.2 ([310abf8](https://github.com/LindemannRock/craft-smart-links/commit/310abf8a302c6d96cbd0bbd73667bf675ec8534e))
* release 1.19.1 ([c1fc18e](https://github.com/LindemannRock/craft-smart-links/commit/c1fc18e529c115fd52c4e465b1cb35d06c9fe2e4))

## [1.19.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.18.0...v1.19.0) (2025-10-02)


### Features

* Add interaction type breakdown to Performance card ([9c47423](https://github.com/LindemannRock/craft-smart-links/commit/9c47423dc73360e24710ff79fcf001badf0d5de9))
* remove redundant enabled and clicks columns from smartlinks table ([ec79d43](https://github.com/LindemannRock/craft-smart-links/commit/ec79d43e4a0b28e4415150a3d6297cdbbe4c069e))


### Bug Fixes

* enabled status requiring two saves to work ([19a7723](https://github.com/LindemannRock/craft-smart-links/commit/19a77233e4ce6add702e903c453217b4d0392fd5))
* enabled status requiring two saves to work ([1106a02](https://github.com/LindemannRock/craft-smart-links/commit/1106a028603c6c0886800ad78cc0822ba66f3b2f))

## [1.19.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.18.0...v1.19.0) (2025-10-01)


### Features

* Add interaction type breakdown to Performance card ([9c47423](https://github.com/LindemannRock/craft-smart-links/commit/9c47423dc73360e24710ff79fcf001badf0d5de9))
* remove redundant enabled and clicks columns from smartlinks table ([ec79d43](https://github.com/LindemannRock/craft-smart-links/commit/ec79d43e4a0b28e4415150a3d6297cdbbe4c069e))

## [1.18.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.17.2...v1.18.0) (2025-10-01)


### Features

* Improve analytics data management and platform display ([d60def7](https://github.com/LindemannRock/craft-smart-links/commit/d60def7a5fa4014e6ed9a201e251bedc28879c4f))


### Bug Fixes

* multi-site analytics tracking ([493bbc4](https://github.com/LindemannRock/craft-smart-links/commit/493bbc427bca5b0ba4e4575f88c4d98ef1405ac9))
* Update URL assignment to check both redirectUrl and buttonUrl formats ([832f196](https://github.com/LindemannRock/craft-smart-links/commit/832f1962242a6be8ac206f21e5d27ea8b1f212bd))

## [1.17.2](https://github.com/LindemannRock/craft-smart-links/compare/v1.17.1...v1.17.2) (2025-10-01)


### Bug Fixes

* Show read-only notice only on Field Layout settings page ([049d7ca](https://github.com/LindemannRock/craft-smart-links/commit/049d7ca021d451ccf7756a1e06af4c4b73949924))

## [1.17.1](https://github.com/LindemannRock/craft-smart-links/compare/v1.17.0...v1.17.1) (2025-10-01)


### Bug Fixes

* Preserve QR source parameter and display destination URLs in analytics ([a579481](https://github.com/LindemannRock/craft-smart-links/commit/a579481cb2fc65772a47fc380405b8c106527f78))

## [1.17.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.16.0...v1.17.0) (2025-10-01)


### Features

* Add read-only mode for Smart Links settings when allowAdminChanges is disabled ([a9ad703](https://github.com/LindemannRock/craft-smart-links/commit/a9ad70344ceaf8b304b848de739600a2d0d00e90))

## [1.16.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.15.0...v1.16.0) (2025-10-01)


### Features

* Add field layout support with project config sync ([21e0ba8](https://github.com/LindemannRock/craft-smart-links/commit/21e0ba8551bd5a7c58e603e93db3651cadcac3cc))

## [1.15.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.14.0...v1.15.0) (2025-10-01)


### Features

* Register project config event handlers and save field layout UID ([3490026](https://github.com/LindemannRock/craft-smart-links/commit/34900265fa6609fd8fbc092d67fa53100dab01dc))

## [1.14.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.13.7...v1.14.0) (2025-10-01)


### Features

* Add Field Layout support to Smart Links element type ([7b77015](https://github.com/LindemannRock/craft-smart-links/commit/7b77015311250dd08af76b7069c8bb3e0d8377eb))

## [1.13.7](https://github.com/LindemannRock/craft-smart-links/compare/v1.13.6...v1.13.7) (2025-10-01)


### Bug Fixes

* smart link tracking to work with static page caching ([1fb2774](https://github.com/LindemannRock/craft-smart-links/commit/1fb2774df9761bd8b8c5c7aaad9cf925ed969add))

## [1.13.6](https://github.com/LindemannRock/craft-smart-links/compare/v1.13.5...v1.13.6) (2025-09-30)


### Bug Fixes

* wait for tracking to complete before redirect ([4400b5e](https://github.com/LindemannRock/craft-smart-links/commit/4400b5e7196541cb65ceaa86b40bbc570594be60))

## [1.13.5](https://github.com/LindemannRock/craft-smart-links/compare/v1.13.4...v1.13.5) (2025-09-30)


### Bug Fixes

* replace sendBeacon with fetch POST for CDN compatibility ([71a62dd](https://github.com/LindemannRock/craft-smart-links/commit/71a62dd917f1a1d8ec8d4b9bf97b8ac11708af59))

## [1.13.4](https://github.com/LindemannRock/craft-smart-links/compare/v1.13.3...v1.13.4) (2025-09-30)


### Bug Fixes

* use action URLs for tracking endpoints to bypass CDN caching ([67fb674](https://github.com/LindemannRock/craft-smart-links/commit/67fb674273cd8649e817fb45a20ba7d4e765bac4))

## [1.13.3](https://github.com/LindemannRock/craft-smart-links/compare/v1.13.2...v1.13.3) (2025-09-30)


### Bug Fixes

* use action URLs for tracking endpoints to bypass CDN caching ([44ba917](https://github.com/LindemannRock/craft-smart-links/commit/44ba917e05622ac04902e6ac4426bccbf675e207))

## [1.13.2](https://github.com/LindemannRock/craft-smart-links/compare/v1.13.1...v1.13.2) (2025-09-30)


### Bug Fixes

* make redirects truly cache-safe by moving URL selection to client-side ([bdbfa15](https://github.com/LindemannRock/craft-smart-links/commit/bdbfa15bacdaf5484602b10e623f935420c509d9))

## [1.13.1](https://github.com/LindemannRock/craft-smart-links/compare/v1.13.0...v1.13.1) (2025-09-30)


### Bug Fixes

* improve tracking and analytics display ([d94701c](https://github.com/LindemannRock/craft-smart-links/commit/d94701c5290c2323bc811a7b1acdf0fd5a8a6f48))

## [1.13.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.12.0...v1.13.0) (2025-09-30)


### Features

* refactor analytics tracking to client-side JavaScript for CDN compatibility ([edfd7a9](https://github.com/LindemannRock/craft-smart-links/commit/edfd7a91bccb7bacc0caeba9ea805e59c2b3cf42))

## [1.12.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.11.0...v1.12.0) (2025-09-30)


### Features

* add QR code cache busting setting to fix tracking with CDN caching ([72eac94](https://github.com/LindemannRock/craft-smart-links/commit/72eac947123e427262617346103543810347fb4d))

## [1.11.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.10.0...v1.11.0) (2025-09-30)


### Features

* enhance settings UI with URL and template configuration options for smart links and QR codes ([239219d](https://github.com/LindemannRock/craft-smart-links/commit/239219d1c4449067f558148b5bab2d1ca0ae7d88))

## [1.10.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.9.2...v1.10.0) (2025-09-30)


### Features

* add configurable URL prefixes for smart links and QR codes ([f7239b2](https://github.com/LindemannRock/craft-smart-links/commit/f7239b2f47d3e3329c1d0bc4dc181e69eb033b4d))
* add custom QR code template settings and update related translations ([c362642](https://github.com/LindemannRock/craft-smart-links/commit/c362642eb71a064e27da7cbc360225efe100ae3e))
* add customizable URL prefixes and templates for smart links and QR codes ([eff264d](https://github.com/LindemannRock/craft-smart-links/commit/eff264d7cc39d6d81d622f1628978a6d261ef28f))

## [1.9.2](https://github.com/LindemannRock/craft-smart-links/compare/v1.9.1...v1.9.2) (2025-09-30)


### Bug Fixes

* update device detection method in RedirectController ([198fc1a](https://github.com/LindemannRock/craft-smart-links/commit/198fc1acadd5a050052b2c1ca8db9343bfea914e))

## [1.9.1](https://github.com/LindemannRock/craft-smart-links/compare/v1.9.0...v1.9.1) (2025-09-30)


### Bug Fixes

* update device detection method in RedirectController ([3e7fb1a](https://github.com/LindemannRock/craft-smart-links/commit/3e7fb1abcfd76bbbbecd9fb4bfca2706edbf47c9))

## [1.9.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.8.0...v1.9.0) (2025-09-30)


### Features

* update caching strategy in RedirectController to vary by device type ([9bb8e4b](https://github.com/LindemannRock/craft-smart-links/commit/9bb8e4bd881509e72fb5f8f60f2c8d9726ddfbc1))

## [1.8.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.7.1...v1.8.0) (2025-09-30)


### Features

* enhance CSRF token response with device detection information ([5af440b](https://github.com/LindemannRock/craft-smart-links/commit/5af440ba912c25b3e97df877cf8f60de1747af26))

## [1.7.1](https://github.com/LindemannRock/craft-smart-links/compare/v1.7.0...v1.7.1) (2025-09-30)


### Bug Fixes

* update site selection logic in multi-site configuration ([d2bd97b](https://github.com/LindemannRock/craft-smart-links/commit/d2bd97baae4bd6f865f9da53621e581f12e36cca))

## [1.7.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.6.0...v1.7.0) (2025-09-30)


### Features

* add CSRF token refresh for cached pages and fix metadata serialization ([c22c2b1](https://github.com/LindemannRock/craft-smart-links/commit/c22c2b138e3c93382621cb7f1fcaaf9999a4c898))


### Bug Fixes

* update instruction for custom redirect template field ([de0a299](https://github.com/LindemannRock/craft-smart-links/commit/de0a299fd959ff56f2f8a48357e0e3424455548f))
* update PHP requirement from ^8.0.2 to ^8.2 in composer.json ([29d375d](https://github.com/LindemannRock/craft-smart-links/commit/29d375d857f2f3eb9277318c24150ac3034e1120))
* use array_key_exists for attribute checks in settings configuration ([31e8b40](https://github.com/LindemannRock/craft-smart-links/commit/31e8b40191b9c7f1d689e86e97a10f26f401a347))

## [1.6.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.5.0...v1.6.0) (2025-09-25)


### Features

* add Smart Links utility template with link statistics and recent analytics ([acf62c7](https://github.com/LindemannRock/craft-smart-links/commit/acf62c7ad344275381fdce7cfbefa74b8f674591))

## [1.5.0](https://github.com/LindemannRock/craft-smart-links/compare/v1.4.2...v1.5.0) (2025-09-24)


### Features

* enhance settings handling with additional debug logging and auto-setting for qrLogoVolumeUid ([a3b7d71](https://github.com/LindemannRock/craft-smart-links/commit/a3b7d7112493de5c0c56a27c29914bf02c87768a))

## [1.4.2](https://github.com/LindemannRock/craft-smart-links/compare/v1.4.1...v1.4.2) (2025-09-24)


### Bug Fixes

* update repository links in README and composer.json to reflect new naming ([a239296](https://github.com/LindemannRock/craft-smart-links/commit/a239296fbe4e9cc70bd86863bd89fbcec3031043))

## [1.4.1](https://github.com/LindemannRock/smart-links/compare/v1.4.0...v1.4.1) (2025-09-24)


### Bug Fixes

* improve description in CleanupAnalyticsJob and format .gitignore entries ([3a58cbc](https://github.com/LindemannRock/smart-links/commit/3a58cbc9cd5403b2413e9a644ec7b7026baab72f))

## [1.4.0](https://github.com/LindemannRock/smart-links/compare/v1.3.0...v1.4.0) (2025-09-15)


### Features

* update README and migration for site settings in Smart Links ([c309b1b](https://github.com/LindemannRock/smart-links/commit/c309b1b98e1a00b75f09c039b6054c736e0ed1b5))

## [1.3.0](https://github.com/LindemannRock/smart-links/compare/v1.2.2...v1.3.0) (2025-09-15)


### Features

* add checkbox group for enabling Smart Links on specific sites ([a0d6f85](https://github.com/LindemannRock/smart-links/commit/a0d6f8586d7135625128b61857bc50d52abcd46d))
* add enabledSites property to Settings model for site-specific Smart Links configuration ([828b105](https://github.com/LindemannRock/smart-links/commit/828b105f4fc2335edec9227be4b0a81198233e31))
* add multi-site management and site selection configuration for Smart Links ([304ebc1](https://github.com/LindemannRock/smart-links/commit/304ebc1470760ad2e8e7f66d11996358bc81f279))
* add site settings and default settings row to smartlinks_settings table ([c143d41](https://github.com/LindemannRock/smart-links/commit/c143d41a1fb7d5b2cd0c2d8deb254284a5bff4e2))
* implement site-specific Smart Links functionality and enable site selection in templates ([6c87105](https://github.com/LindemannRock/smart-links/commit/6c871052fcfa89f39611b97ed62c4bd2d1a04d60))

## [1.2.2](https://github.com/LindemannRock/smart-links/compare/v1.2.1...v1.2.2) (2025-09-15)


### Bug Fixes

* handle empty QR logo and image IDs in SmartLinksController ([d9a7e65](https://github.com/LindemannRock/smart-links/commit/d9a7e65055ca27534f382ad29aec7a95eeaa10e7))

## [1.2.1](https://github.com/LindemannRock/smart-links/compare/v1.2.0...v1.2.1) (2025-09-15)


### Bug Fixes

* update copyright notice in LICENSE file ([3a2531c](https://github.com/LindemannRock/smart-links/commit/3a2531cd2086d5dddc2e7a16905ed3ae6fa35f05))

## [1.2.0](https://github.com/LindemannRock/smart-links/compare/v1.1.0...v1.2.0) (2025-09-14)


### Features

* add plugin credit component to settings and analytics templates ([c22cf96](https://github.com/LindemannRock/smart-links/commit/c22cf96fde791c79b1e650964985cf44f8beeba6))

## [1.1.0](https://github.com/LindemannRock/smart-links/compare/v1.0.4...v1.1.0) (2025-09-11)


### Features

* enhance README with additional features for image management and landing page customization ([8162b36](https://github.com/LindemannRock/smart-links/commit/8162b36ffec42db3b9701d2cf6dd96cf92f9617f))


### Bug Fixes

* Smart Links database schema to match working installation ([03fe1dd](https://github.com/LindemannRock/smart-links/commit/03fe1dd45e8985bafe8996f3b38dde2d01740057))

## [1.0.4](https://github.com/LindemannRock/smart-links/compare/v1.0.3...v1.0.4) (2025-09-10)


### Bug Fixes

* update requirements in README for clarity and consistency ([a17ca25](https://github.com/LindemannRock/smart-links/commit/a17ca2501f162c2c60df0f82449f142f5337d7e3))

## [1.0.3](https://github.com/LindemannRock/smart-links/compare/v1.0.2...v1.0.3) (2025-09-10)


### Bug Fixes

* update displayName method to return plugin name and rename iconPath to icon ([aca60a0](https://github.com/LindemannRock/smart-links/commit/aca60a06bc689820a2d407270541e1c4222d5853))

## [1.0.2](https://github.com/LindemannRock/smart-links/compare/v1.0.1...v1.0.2) (2025-09-02)


### Bug Fixes

* remove development backups and IDE files ([f078fdb](https://github.com/LindemannRock/smart-links/commit/f078fdb024b40398b2ad93c9d9499ffc9172a021))

## 1.0.1 (2025-09-02)


### Features

* initial Smart Links plugin implementation ([6b5c0ed](https://github.com/LindemannRock/smart-links/commit/6b5c0ed5911f8ecdb803cb0c76395fdce7bb03ef))

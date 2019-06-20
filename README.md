# ArcAds ACM Provider

This plugin extends the [Ad Code Manager plugin](https://github.com/Automattic/Ad-Code-Manager) to provide functionality for the ArcAds wrapper, which is itself a wrapper on DFP.

## About ArcAds

ArcAds was built by Arc Publishing for use at the Washington Post. Later they open sourced it.

The code, and documentation about the JavaScript it needs to generate, is in [this GitHub repository](https://github.com/washingtonpost/ArcAds).

## About this plugin

This plugin seeks to support the functionality that ArcAds can generate by using the functionality within the [Ad Code Manager](https://wordpress.org/plugins/ad-code-manager/) plugin.

This means that ArcAds becomes its own provider that can be used within ACM. It does not interact with the included DFP Async provider.
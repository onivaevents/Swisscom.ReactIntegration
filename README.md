# create-react-app Flow Integration

[create-react-app (CRA)](https://github.com/facebook/create-react-app) is specifically optimized for single-page apps;
but through some workarounds it can be used for developing JS for multi-page apps as well. This package bundles
these workarounds.

The main problem of CRA is that there is no default way of running the dev-server watch-mode, and have it emit
static JS files which we can simply include. This might be solved as soon as [this PR](https://github.com/facebook/create-react-app/pull/6144)
is merged in CRA.

Furthermore, CRA employs code splitting; so during development the URLs of the dev server change very often.


## Features

This package provides a `Swisscom.ReactIntegration:ReactScripts` Fusion prototype, which renders the necessary
`script` tags. In development mode, it extracts the script tag URLs from the CRA dev-server (by CURL'ing to http://localhost:3000);
and in production mode it simply emits the built files.

Example:

```
page.body.javascripts.reactScripts = Swisscom.ReactIntegration:ReactScripts {
    # path to the JavaScript *folder* of the built react app. Required.
    path = 'resource://Your.Package/Public/BuiltReactApp/js'
    
    # optional: path to the dev server
    developmentServerUrl = 'http://other-ip-here:3000'
}
```

If the setting `Swisscom.ReactIntegration.productionMode` is true (default in `Production` context), we load
the built JS. Otherwise, we load the scripts from the CRA dev server URL.


## Installation

1. Install this package as usual.

2. kickstart your react app in `[YourPackage]/Resources/Private/react-app` using create-react-app.

3. Add a symlink from `[YourPackage]/Resources/Public/BuiltReactApp` to `[YourPackage]/Private/JavaScript/build/static`; e.g.
   by typing `ln -s ../Private/JavaScript/build/static BuiltReactApp`.

4. Add the following Fusion to your Page:

   ```
   page.body.javascripts.reactScripts = Swisscom.ReactIntegration:ReactScripts {
       # path to the JavaScript *folder* of the built react app. Required.
       path = 'resource://YourPackage/Public/BuiltReactApp/js'
   }
   ```


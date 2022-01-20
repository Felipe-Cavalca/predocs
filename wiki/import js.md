
```
// arquivo teste.js
export function testandoImport() {
    console.log('olá');
}
```

```
// arquivo app.js
import { testandoImport } from './teste.js'
testandoImport();
```

```
<script type="module" src="js/app.js"></script>
```
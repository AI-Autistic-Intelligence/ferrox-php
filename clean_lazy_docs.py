import os
import re

directory = "docs/modules"

replacements = [
    (r"The `(.*?)` is responsible for enterprise-grade execution of operations within `(.*?)`\.", 
     r"The `\1` class provides the architectural boundary and core abstractions mapped directly to `\2`."),
    (r"Initializes a new instance of the class, enforcing strict constructor Dependency Injection \(IoC\) to guarantee internal memory-safety and immutability\.",
     r"Constructs the object via strict Dependency Injection, enforcing structural immutability."),
    (r"Executes the `(.*?)` domain logic securely\. Enforces strict type constraints, adhering to Ferrox's Zero-Trust and memory-safe paradigms\.",
     r"Executes `\1` with strict type safety and boundary validation.")
]

for filename in os.listdir(directory):
    if not filename.endswith(".md"): continue
    if filename in ["ferrox-php-utils.md", "ferrox-php-core.md"]: continue
    
    filepath = os.path.join(directory, filename)
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
        
    for old, new in replacements:
        content = re.sub(old, new, content)
        
    with open(filepath, "w", encoding="utf-8") as f:
        f.write(content)

print("Cleaned up lazy AI boilerplate across remaining modules.")

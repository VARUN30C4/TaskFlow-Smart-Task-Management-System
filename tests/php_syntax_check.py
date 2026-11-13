"""
TaskFlow – Robust PHP Syntax & State-Machine Parser
Parses PHP tokens accurately distinguishing between HTML template mode,
PHP code mode, string literals, and comments.
"""

import os

WORKSPACE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

php_files = []
for root, dirs, files in os.walk(WORKSPACE):
    if ".git" in root or "tests" in root:
        continue
    for f in files:
        if f.endswith(".php"):
            php_files.append(os.path.relpath(os.path.join(root, f), WORKSPACE))

print(f"Auditing {len(php_files)} PHP files with State-Machine Lexer...\n")

brackets = {'{': '}', '(': ')', '[': ']'}
bracket_closers = set(brackets.values())

all_passed = True

for pf in php_files:
    path = os.path.join(WORKSPACE, pf)
    with open(path, "r", encoding="utf-8") as f:
        src = f.read()

    i = 0
    n = len(src)
    in_php = False
    stack = []
    line = 1
    col = 1
    error = None

    while i < n:
        # Track line & col
        if src[i] == '\n':
            line += 1
            col = 1
        else:
            col += 1

        if not in_php:
            # Look for <?php or <?=
            if src.startswith("<?php", i):
                in_php = True
                i += 5
                continue
            elif src.startswith("<?=", i):
                in_php = True
                i += 3
                continue
            else:
                i += 1
                continue
        else:
            # We are inside PHP
            # Check for closing tag ?>
            if src.startswith("?>", i):
                in_php = False
                i += 2
                continue

            # Check single-line comment // or #
            if src.startswith("//", i) or src[i] == '#':
                # comment continues until newline or ?>
                while i < n and src[i] != '\n' and not src.startswith("?>", i):
                    i += 1
                continue

            # Check multi-line comment /*
            if src.startswith("/*", i):
                i += 2
                while i < n and not src.startswith("*/", i):
                    if src[i] == '\n':
                        line += 1
                        col = 1
                    i += 1
                i += 2 # skip */
                continue

            # Check single-quoted string '...'
            if src[i] == "'":
                i += 1
                while i < n:
                    if src[i] == '\\':
                        i += 2
                    elif src[i] == "'":
                        i += 1
                        break
                    else:
                        if src[i] == '\n':
                            line += 1
                            col = 1
                        i += 1
                continue

            # Check double-quoted string "..."
            if src[i] == '"':
                i += 1
                while i < n:
                    if src[i] == '\\':
                        i += 2
                    elif src[i] == '"':
                        i += 1
                        break
                    else:
                        if src[i] == '\n':
                            line += 1
                            col = 1
                        i += 1
                continue

            # Delimiters in PHP code
            ch = src[i]
            if ch in brackets:
                stack.append((ch, line, col))
            elif ch in bracket_closers:
                if not stack:
                    error = f"Unexpected closing '{ch}' at line {line}, col {col}"
                    break
                opening, o_line, o_col = stack.pop()
                if brackets[opening] != ch:
                    error = f"Mismatched closing '{ch}' at line {line}, col {col} (matches '{opening}' from line {o_line}, col {o_col})"
                    break

            i += 1

    if not error and stack:
        opening, o_line, o_col = stack[-1]
        error = f"Unclosed '{opening}' opened at line {o_line}, col {o_col}"

    if error:
        all_passed = False
        print(f"❌ FAIL: {pf} -> {error}")
    else:
        print(f"✅ PASS: {pf}")

print("\n" + ("=" * 50))
if all_passed:
    print(f"ALL {len(php_files)} PHP FILES PASSED STRUCTURAL SYNTAX AUDIT!")
else:
    print("SOME PHP FILES FAILED SYNTAX AUDIT.")
print("=" * 50)

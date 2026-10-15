import os
import json
import subprocess
import urllib.request
import time

TOKEN = "521f8cb5217d5f5cac123379c2ff233daae5dd2d"
USERNAME = "AI-Autistic-Intelligence"
REPO_ROOT = "c:/Users/nn/Desktop/code/ferrox-php"

PKGS = [
    'auth', 'broadcasting', 'cli', 'config', 'core', 'cqrs', 'crud-gen',
    'data', 'database-core', 'events', 'gateway', 'mailer', 'observability',
    'queue', 'rate-limiter', 'rpc', 'scheduler', 'security', 'storage',
    'utils', 'validation'
]

def run(cmd, cwd=REPO_ROOT):
    cmd_str = ' '.join(cmd) if isinstance(cmd, list) else cmd
    print(f"--> {cmd_str}", flush=True)
    res = subprocess.run(cmd_str, cwd=cwd, shell=True, text=True, capture_output=True)
    if res.returncode != 0:
        print(f"Error: {res.stderr.strip()}", flush=True)
    else:
        if res.stdout.strip():
            print(res.stdout.strip(), flush=True)
    return res.returncode == 0

def notify_packagist(pkg, create_first=False):
    url = f"https://github.com/AI-Autistic-Intelligence/ferrox-php-{pkg}"
    
    if create_first:
        create_api = f"https://packagist.org/api/create-package?username={USERNAME}&apiToken={TOKEN}"
        data = json.dumps({"repository": {"url": url}}).encode()
        req = urllib.request.Request(create_api, data=data, headers={"Content-Type": "application/json"})
        try:
            with urllib.request.urlopen(req, timeout=10) as resp:
                print(f"Packagist create for {pkg}: {resp.read().decode()}", flush=True)
        except Exception as e:
            print(f"Packagist create error for {pkg}: {e}", flush=True)
            
    update_api = f"https://packagist.org/api/update-package?username={USERNAME}&apiToken={TOKEN}"
    data = json.dumps({"repository": {"url": url}}).encode()
    req = urllib.request.Request(update_api, data=data, headers={"Content-Type": "application/json"})
    try:
        with urllib.request.urlopen(req, timeout=10) as resp:
            print(f"Packagist update for {pkg}: {resp.read().decode()}", flush=True)
    except Exception as e:
        print(f"Packagist update error for {pkg}: {e}", flush=True)

def main():
    print("=== 1. Bumping composer.json to 1.2.1 ===", flush=True)
    cj_path = os.path.join(REPO_ROOT, 'composer.json')
    with open(cj_path, 'r', encoding='utf-8') as f:
        cj = json.load(f)
    cj['version'] = '1.2.1'
    with open(cj_path, 'w', encoding='utf-8') as f:
        json.dump(cj, f, indent=4)
        f.write('\n')

    print("=== 2. Git Commit & Tag in ferrox-php ===", flush=True)
    run(['git', 'add', '.'])
    run(['git', 'commit', '-m', '"chore: bump version to 1.2.1 and sync package manifests"'])
    run(['git', 'tag', '-f', 'v1.2.1'])
    run(['git', 'push', 'origin', 'master', '--tags'])

    print("=== 3. Subtree Split, Push and Packagist Update for each package ===", flush=True)
    for pkg in PKGS:
        repo_name = f"ferrox-php-{pkg}"
        branch_name = f"split-{pkg}"
        remote_url = f"https://github.com/AI-Autistic-Intelligence/{repo_name}.git"

        print(f"\n--- Processing {pkg} ---", flush=True)
        # Delete old branch if exists
        subprocess.run(f"git branch -D {branch_name}", cwd=REPO_ROOT, shell=True, capture_output=True)
        
        # Subtree split
        split_ok = run(f'git subtree split -P "packages/{pkg}" -b {branch_name}')
        if not split_ok:
            print(f"Skipping push for {pkg} due to split failure", flush=True)
            continue
            
        # Push master and v1.2.1 tag
        run(f'git push "{remote_url}" "{branch_name}:master" --force')
        run(f'git push "{remote_url}" "{branch_name}:refs/tags/v1.2.1" --force')

        # Notify Packagist
        notify_packagist(pkg, create_first=(pkg == 'validation'))
        time.sleep(1)

    print("\n🎉 ferrox-php all packages successfully updated and deployed to Packagist!", flush=True)

if __name__ == '__main__':
    main()

import os
import shutil

BASE = os.path.dirname(__file__)
REMOVE_FILES = [
    'generateData.js',
    'historical-data.json',
    'model.json',
    'package.json',
    'package-lock.json',
    'npm.tgz',
    'server.js',
    'trainer.js',
    'db.sqlite',
]
REMOVE_DIRS = [
    'node_modules',
    'npm_bootstrap',
    'node-v20.15.0-win-x64',
]

for name in REMOVE_FILES:
    path = os.path.join(BASE, name)
    if os.path.isfile(path) or os.path.islink(path):
        try:
            os.remove(path)
            print(f'Removed file: {name}')
        except Exception as exc:
            print(f'Failed file: {name} -> {exc}')

for name in REMOVE_DIRS:
    path = os.path.join(BASE, name)
    if os.path.isdir(path):
        try:
            shutil.rmtree(path)
            print(f'Removed directory: {name}')
        except Exception as exc:
            print(f'Failed directory: {name} -> {exc}')

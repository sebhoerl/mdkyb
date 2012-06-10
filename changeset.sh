#!/bin/bash
echo "Type the revision id, followed by [ENTER]:"
read rev

rm -rf changeset
mkdir changeset

for file in `git diff --name-only $rev`
do
    cp --parents $file changeset
done

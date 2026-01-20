#!/bin/sh

set -eu

usage() {
  cat <<'USAGE'
Usage:
  PROJECT=project_name bin/docker/run_example.sh <transformation.yaml> [console options...]

Examples:
  PROJECT=project_name bin/docker/run_example.sh transformation_in_steps_main.yaml
  PROJECT=project_name bin/docker/run_example.sh transformation_in_steps_main.yaml --debug --try 10
USAGE
}

if [ "${1:-}" = "-h" ] || [ "${1:-}" = "--help" ]; then
  usage
  exit 0
fi

if [ -z "${PROJECT:-}" ]; then
  echo "Error: PROJECT is not set." >&2
  usage >&2
  exit 1
fi

if [ -z "${1:-}" ]; then
  echo "Error: transformation file argument is required." >&2
  usage >&2
  exit 1
fi

transformation_file=$1
shift

example_root="examples/${PROJECT}"
workpath="${example_root}/workpath"
sources="${example_root}/sources"
added_sources="${example_root}/added_sources"
extensions="${example_root}/extensions"

mkdir -p "${example_root}/transformation" "${workpath}" "${sources}" "${added_sources}" "${extensions}"

transformation_path="${example_root}/transformation/${transformation_file}"

if [ ! -f "${transformation_path}" ]; then
  cat > "${transformation_path}" <<'YAML'
# Auto-generated template. Fill in your pipeline and re-run this script.

pipeline:
  input:
    reader:
      type: csv
      filename: 'input.csv'
  actions: {}
  output:
    writer:
      type: csv
      filename: 'output.csv'
YAML
  echo "Created template: ${transformation_path}" >&2
  echo "Edit the file and re-run the command." >&2
  exit 1
fi

exec bin/docker/console transformation \
  --file "${transformation_path}" \
  --workpath "${workpath}" \
  --source "${sources}" \
  --addSource "${added_sources}" \
  --extensions "${extensions}" \
  "$@"

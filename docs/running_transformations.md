# Running Transformations via the CLI

This guide explains how to execute transformation processes using the CLI in the parsable-file-multi-tool project. It covers the typical workflow, command structure, and best practices for running transformations in your development or production environment.

---

## 1. Overview
Transformations are run from the command line using a combination of shell scripts and PHP commands. The most common entry point is the `bin/docker/run_example.sh` script, which sets up the required directory structure and invokes the transformation command with the appropriate arguments.

---

## 2. Typical Command Usage
To run a transformation for a specific project, use the following command:

```sh
PROJECT=<project> bin/docker/run_example.sh main-STEPS.yaml
```

- Replace `<project>` with the name of your project directory under `examples/`.
- Replace `main-STEPS.yaml` with the name of your transformation steps YAML file (usually located in `examples/<project>/transformation/`).

This command will:
- Ensure all necessary directories exist for the project (sources, workpath, transformation, etc.).
- Call the main transformation CLI with the correct file and directory arguments.

---

## 3. How It Works
The `run_example.sh` script wraps the main transformation command, which is defined in PHP (`src/Command/TransformationCommand.php`). It passes the following arguments:
- `--file`: Path to the transformation steps YAML file
- `--workpath`: Directory for intermediate and output files
- `--source`: Directory containing source data files
- `--addSource`, `--extensions`: Optional directories for additional sources and extensions

You can pass additional arguments to the script as needed, and they will be forwarded to the transformation command.

---

## 4. Example Directory Structure
```
examples/<project>/
  transformation/
    main-STEPS.yaml
  sources/
  workpath/
  added_sources/
  extensions/
```

---

## 5. Advanced Usage
- You can create multiple transformation YAML files for different workflows or data sets.
- Use the `secrets.yaml` file in the transformation directory to provide sensitive credentials or environment-specific configuration.
- Pass extra CLI arguments (such as `--debug` or `--line`) to control the transformation process.

---

## 6. Best Practices
- Always version control your transformation YAML files and scripts.
- Keep secrets and credentials in `secrets.yaml`, which should be excluded from version control.
- Use descriptive names for your transformation step files.
- Review logs and output in the `workpath` directory after each run.

---

For more details on the structure of transformation YAML files, see `docs/transformation_steps.md`.


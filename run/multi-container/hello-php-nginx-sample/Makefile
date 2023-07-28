PROJECT_NAME  ?= ${PROJECT_ID}
REPO_NAME     ?= ${REPO_NAME}
REGISTRY      ?= ${REGION}-docker.pkg.dev
MC_SERVICE_NAME ?= ${MC_SERVICE_NAME}

login: ## [project] Log-in to registry
	gcloud auth login
	gcloud artifacts repositories create ${REPO_NAME} --repository-format=docker

build: ## [project] Build images to Artifact Registry
	gcloud builds submit --tag=${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPO_NAME}/nginx ./nginx
	gcloud builds submit --tag=${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPO_NAME}/php ./php-app

deploy: ## [project] Deploy to Cloud Run MC service
	sed -i -e s/MC_SERVICE_NAME/${MC_SERVICE_NAME}/g -e s/REGION/${REGION}/g -e s/REPO_NAME/${REPO_NAME} service.yaml
	gcloud run services replace service.yaml

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'


#include "cuda_runtime.h"
#include "device_launch_parameters.h"
#include <math.h>
#include <stdio.h>
#include <curand.h>
#include <curand_kernel.h>
#include <stdlib.h>
#include <time.h>
#include <string>
#include <iostream>
#include <fstream>
#include <nlohmann/json.hpp>
#include <cstring>
#include <direct.h>

// parents evening timetabler
void shuffleSame(int *array1, int *array2, size_t n)
{
	if (n > 1)
	{
		// set the seed
		srand(time(NULL));
		size_t i;
		for (i = 0; i < n - 1; i++)
		{
			size_t j = i + rand() / (RAND_MAX / (n - i) + 1);
			int t1 = array1[j];
			int t2 = array2[j];
			array1[j] = array1[i];
			array2[j] = array2[i];
			array1[i] = t1;
			array2[i] = t2;
		}
	}
}

__global__ void initRand(curandState *state, unsigned long seed)
{
	int id = threadIdx.x + blockIdx.x * blockDim.x;
	curand_init(seed + id, id, 0, &state[id]);
}

__global__ void bundledFunction(curandState *state, int *appTimes, int *teachers, int *teacherMin, int *teacherMax, int numTeachers, int *parents, int *parentMin, int *parentMax, int numParents, int numSlots, int numThreads, int numAppointments, int *teacherTimes, int *parentTimes, int maxTeacher, int maxParent, int *fitnesses, int startIndex)
{
	const unsigned int id = threadIdx.x + blockIdx.x * blockDim.x;
	if (id >= numThreads)
	{
		return; // if the thread is out of bounds return
	}
	__shared__ int fitness[4096];
	if (fitness[id] != 0)
	{
		fitness[id] = 0;
	}
	// print numAppointments
	// print the parents array
	for (int currentMax = startIndex; currentMax < numAppointments; currentMax++)
	{
		// set all times to -1
		for (int i = 0; i < numSlots; i++)
		{
			teacherTimes[id * numSlots + i] = -1;
			parentTimes[id * numSlots + i] = -1;
		}
		for (int i = 0; i < currentMax; i++)
		{
			if ((teachers[i] == teachers[currentMax]) && (appTimes[id * numAppointments + i] != -2))
			{
				int j = -1;
				do
				{
					j++;
				} while (teacherTimes[id * numSlots + j] != -1);
				teacherTimes[id * numSlots + j] = appTimes[id * numAppointments + i];
			}

			if ((parents[i] == parents[currentMax]) && (appTimes[id * numAppointments + i] != -2))
			{
				int j = -1;
				do
				{
					j++;
				} while ((parentTimes[id * numSlots + j] != -1));
				parentTimes[id * numSlots + j] = appTimes[id * numAppointments + i];
			}
		}

		bool valid = true;
		int gMin = 0;
		int gMax = numSlots;

		// range between the highest min and lowest max

		if (teacherMin[teachers[currentMax]] > parentMin[parents[currentMax]])
		{
			gMin = teacherMin[teachers[currentMax]];
		}
		else
		{
			gMin = parentMin[parents[currentMax]];
		}
		if (teacherMax[teachers[currentMax]] < parentMax[parents[currentMax]])
		{
			gMax = teacherMax[teachers[currentMax]];
		}
		else
		{
			gMax = parentMax[parents[currentMax]];
		}
		int range = gMax - gMin;
		//if id is 0 print the range
		for (int i = 0; i < range; i++)
		{
			valid = true; // Reset valid to true at the start of each iteration
			// check that the teacher is free
			bool teacherFree = true;
			for (int j = 0; j < numSlots; j++)
			{
				if (teacherTimes[id * numSlots + j] == i + gMin)
				{
					teacherFree = false;
					break; // No need to check further if teacher is not free
				}
			}

			// check that the parent is free
			bool parentFree = true;
			for (int j = 0; j < numSlots; j++)
			{
				if (parentTimes[id * numSlots + j] == i + gMin)
				{
					parentFree = false;
					break; // No need to check further if parent is not free
				}
			}

			if (!(teacherFree && parentFree))
			{
				valid = false;
			}

			if (valid) // If valid is true, no need to check further time slots
			{
				break;
			}
		}
		//  if the appointment is valid loop until a valid time is found
		if (valid)
		{
			while (true)
			{

				// generate a random time between the highest min and lowest max
				int time = curand(&state[id]) % range + gMin;
				// check that the teacher is free
				bool teacherFree = true;
				for (int j = 0; j < numSlots; j++)
				{
					if (teacherTimes[id * numSlots + j] == time)
					{
						teacherFree = false;
					}
				}
				// check that the parent is free
				bool parentFree = true;
				for (int j = 0; j < numSlots; j++)
				{
					if (parentTimes[id * numSlots + j] == time)
					{
						parentFree = false;
					}
				}
				if (teacherFree && parentFree)
				{
					appTimes[id * numAppointments + currentMax] = time;
					break;
				}
			}
		}
		else
		{
			// if the appointment is not valid set the time to -2
			appTimes[id * numAppointments + currentMax] = -2;
		}
	}

	for (int i = 0; i < numAppointments; i++)
	{
		// if the appointment is not valid
		if (appTimes[id * numAppointments + i] == -2)
		{
			fitness[id] -= 10;
			continue;
		}

		for (int j = i + 1; j < numAppointments; j++)
		{
			// if appointment is invalid
			if (appTimes[id * numAppointments + j] == -2)
			{
				continue;
			}
			if ((parents[i] != 0) && (parents[i] == parents[j]))
			{
				// if appointments are on the same slot and with the same parent
				if (appTimes[id * numAppointments + i] == appTimes[id * numAppointments + j])
				{
					fitness[id] -= 6;
				}
				// if appointments have 1 slot in between
				if (abs(appTimes[id * numAppointments + i] - appTimes[id * numAppointments + j]) == 2)
				{
					fitness[id] += 3;
				}
				// if appointments have 2 slots in between
				if (abs(appTimes[id * numAppointments + i] - appTimes[id * numAppointments + j]) == 3)
				{
					fitness[id] += 1;
				}
				// if they are consecutive slots
				if (abs(appTimes[id * numAppointments + i] == appTimes[id * numAppointments + j]) == 1)
				{
					fitness[id] -= 1;
				}
			}
		}
	}
	fitnesses[id] = fitness[id];
}

int main()
{
	char cwd[FILENAME_MAX];
	if (_getcwd(cwd, sizeof(cwd)) != NULL) {
		std::cout << "Current working directory: " << cwd << std::endl;
	}
	else {
		std::cerr << "getcwd() error" << std::endl;
		return 1;
	}
	const int numThreads = 4096;
	const int threadsPerBlock = 256;
	const int iterations = 4096;
	nlohmann::json js;
	//print current directory
	

	// Variables related to file input
	try {
		std::ifstream file("input.json");
		file >> js;
	}
	catch (std::exception &e) {
		std::cerr << "Error: " << e.what() << std::endl;
		return 1;
	}

	// Variables related to appointments
	const int numParents = js["parents"].size() + 1;
	const int numSlots = js["duration"];
	const int numTeachers = js["teachers"].size() + 1;
	const int numAppointments = js["appointments"].size();
	const int numBlocked = js["blockedTeacher"].size() + js["blockedParent"].size();
	const int wantedAppointments = js["wantedAppointments"].size();
	printf("Num Wantged: %d\n", wantedAppointments);

	// Variables related to CUDA
	const int blocksPerGrid = (numThreads + threadsPerBlock - 1) / threadsPerBlock;

	// arrays
	int *teachers = (int *)malloc((numAppointments + numBlocked + wantedAppointments) * sizeof(int) * 2);
	int *parents = (int *)malloc((numAppointments + numBlocked + wantedAppointments) * sizeof(int) * 2);
	int *teacherMin = (int *)malloc((numTeachers + 1) * sizeof(int) * 2);
	int *teacherMax = (int *)malloc((numTeachers + 1) * sizeof(int) * 2);
	int *parentMin = (int *)malloc((numParents + 1) * sizeof(int) * 2);
	int *parentMax = (int *)malloc((numParents + 1) * sizeof(int) * 2);
	int *teacherMap = (int *)malloc((numTeachers + 1) * sizeof(int) * 2);
	int *parentMap = (int *)malloc((numParents + 1) * sizeof(int) * 2);
	int *singleTimes = (int *)malloc((numAppointments + numBlocked + wantedAppointments) * sizeof(int) * 2);
	int *appTimes = (int *)malloc((numAppointments + numBlocked + wantedAppointments) * numThreads * sizeof(int) * 2);
	int *fitness = (int *)malloc(numThreads * sizeof(int) * 2);
	int *bestTimes = (int *)malloc(iterations * (numAppointments + numBlocked + wantedAppointments) * sizeof(int) * 2 * 2);
	int *bestFitnesses = (int *)malloc(iterations * 2 * sizeof(int) * 2);


	// variables to store the order of appointments for previous iterations
	int *teachersStore = (int*)malloc((numAppointments + numBlocked + wantedAppointments) * iterations * sizeof(int) * 2);
	int *parentsStore = (int*)malloc((numAppointments + numBlocked + wantedAppointments) * iterations * sizeof(int) * 2);

	// Initialize maps
	parentMap[0] = 0;
	teacherMap[0] = 0;

	for (int i = 0; i < numTeachers; i++)
	{
		teacherMin[i] = 0;
		teacherMax[i] = numSlots - 1;
	}
	for (int i = 0; i < numParents; i++)
	{
		parentMin[i] = 0;
		parentMax[i] = numSlots - 1;
	}
	// fill teacher and parent maps
	for (int i = 0; i < numTeachers - 1; i++)
	{
		teacherMap[i + 1] = js["teachers"][i];
	}
	for (int i = 0; i < numParents - 1; i++)
	{
		parentMap[i + 1] = js["parents"][i];
	}
	for (int i = 0; i < js["blockedTeacher"].size(); i++)
	{
		for (int j = 0; j < numTeachers; j++)
		{
			if (teacherMap[j + 1] == js["blockedTeacher"][i]["teacher"])
			{
				teachers[i] = j + 1;
				break;
			}
		}
		parents[i] = 0;
		singleTimes[i] = js["blockedTeacher"][i]["slot"];
	}
	for (int i = 0; i < js["blockedParent"].size(); i++)
	{
		for (int j = 0; j < numParents; j++)
		{
			if (parentMap[j + 1] == js["blockedParent"][i]["parent"])
			{
				parents[js["blockedTeacher"].size() + i] = j + 1;
				break;
			}
		}
		teachers[js["blockedTeacher"].size() + i] = 0;
		singleTimes[js["blockedTeacher"].size() + i] = js["blockedParent"][i]["slot"];
	}
	int *wantedTeachersTmp = (int *)malloc(wantedAppointments * sizeof(int) * 2);
	int *wantedParentsTmp = (int *)malloc(wantedAppointments * sizeof(int) * 2);
	for (int i = 0; i < wantedAppointments; i++)
	{
		for (int j = 0; j < numTeachers; j++)
		{
			if (teacherMap[j + 1] == js["wantedAppointments"][i]["teacher"])
			{
				wantedTeachersTmp[i] = j + 1;
				break;
			}
		}
		for (int j = 0; j < numParents; j++)
		{
			if (parentMap[j + 1] == js["wantedAppointments"][i]["parent"])
			{
				wantedParentsTmp[i] = j + 1;
				break;
			}
		}
		singleTimes[i + numBlocked] = 0;
	}
	// shuffle the wanted appointments
	// print the wanted appointments

	int *teachersTmp = (int *)malloc(js["appointments"].size() * sizeof(int) * 2);
	int *parentsTmp = (int *)malloc(js["appointments"].size() * sizeof(int) * 2);
	for (int i = 0; i < js["appointments"].size(); i++)
	{
		for (int j = 0; j < numTeachers - 1; j++)
		{
			if (teacherMap[j + 1] == js["appointments"][i]["teacher"])
			{
				teachersTmp[i] = j + 1;
				break;
			}
		}
		for (int j = 0; j < numParents - 1; j++)
		{
			if (parentMap[j + 1] == js["appointments"][i]["parent"])
			{
				parentsTmp[i] = j + 1;
				break;
			}
		}
		singleTimes[i + numBlocked + wantedAppointments] = 0;
	}
	for (int i = 0; i < numTeachers - 1; i++)
	{
		teacherMin[i + 1] = js["teacherMin"][i];
	}
	// teacher max
	for (int i = 0; i < numTeachers - 1; i++)
	{
		teacherMax[i + 1] = js["teacherMax"][i] - 1;
	}
	// parent min
	for (int i = 0; i < numParents - 1; i++)
	{
		parentMin[i + 1] = js["parentMin"][i];
	}
	// parent max
	for (int i = 0; i < numParents - 1; i++)
	{
		parentMax[i + 1] = js["parentMax"][i] - 1;
	}
	for (int Itr = 0; Itr < iterations; Itr++)
	{
		// set them all min and max to 0 and 16

		// fill teacher and parent arrays
		// loop through the blocked slots first
		shuffleSame(wantedTeachersTmp, wantedParentsTmp, wantedAppointments);

		// shuffle the appointments
		shuffleSame(teachersTmp, parentsTmp, numAppointments);
		// merge the wanted and normal appointments
		for (int i = 0; i < wantedAppointments; i++)
		{
			teachers[i + numBlocked] = wantedTeachersTmp[i];
			parents[i + numBlocked] = wantedParentsTmp[i];
		}
		for (int i = 0; i < numAppointments; i++)
		{
			teachers[i + numBlocked + wantedAppointments] = teachersTmp[i];
			parents[i + numBlocked + wantedAppointments] = parentsTmp[i];
		}
		// teacher min
		
		//store the parents and teachers arrays for the current iteration
		for (int i = 0; i < numAppointments + numBlocked + wantedAppointments; i++)
		{
			teachersStore[Itr * (numAppointments + numBlocked + wantedAppointments) + i] = teachers[i];
			parentsStore[Itr * (numAppointments + numBlocked + wantedAppointments) + i] = parents[i];
		}

		// allocate memory
		// duplicate singleTimes array

		int count = 0;
		for (int i = 0; i < numThreads; i++)
		{
			std::memcpy(&appTimes[i * (numAppointments + numBlocked + wantedAppointments)], singleTimes, (numAppointments + numBlocked + wantedAppointments) * sizeof(*singleTimes));
			count += (numAppointments + numBlocked + wantedAppointments);
		}
		// free memory

		curandState *d_States;
		cudaMalloc(&d_States, numThreads * sizeof(curandState));

		int *d_teachers;
		cudaMalloc(&d_teachers, (numAppointments + numBlocked + wantedAppointments) * sizeof(int) * 2);
		cudaDeviceSynchronize();
		cudaMemcpy(d_teachers, teachers, (numAppointments + numBlocked + wantedAppointments) * sizeof(int) * 2, cudaMemcpyHostToDevice);
		cudaDeviceSynchronize();

		int *d_teacherMin;
		cudaMalloc(&d_teacherMin, numTeachers * sizeof(int) * 2);
		cudaDeviceSynchronize();
		cudaMemcpy(d_teacherMin, teacherMin, numTeachers * sizeof(int) * 2, cudaMemcpyHostToDevice);
		cudaDeviceSynchronize();

		int *d_teacherMax;
		cudaMalloc(&d_teacherMax, numTeachers * sizeof(int) * 2);
		cudaDeviceSynchronize();
		cudaMemcpy(d_teacherMax, teacherMax, numTeachers * sizeof(int) * 2, cudaMemcpyHostToDevice);
		cudaDeviceSynchronize();

		int *d_appTimes;
		cudaMalloc(&d_appTimes, numThreads * (numAppointments + numBlocked + wantedAppointments) * sizeof(int) * 2);
		cudaDeviceSynchronize();
		cudaMemcpy(d_appTimes, appTimes, (numAppointments + numBlocked + wantedAppointments)* numThreads * sizeof(int) * 2, cudaMemcpyHostToDevice);
		cudaDeviceSynchronize();

		int *d_parents;
		cudaMalloc(&d_parents, (numAppointments + numBlocked + wantedAppointments) * sizeof(int) * 2);
		cudaDeviceSynchronize();
		cudaMemcpy(d_parents, parents, (numAppointments + numBlocked + wantedAppointments) * sizeof(int) * 2, cudaMemcpyHostToDevice);
		cudaDeviceSynchronize();
		// print parents array

		// copy parent arrays to host
		cudaMemcpy(parents, d_parents, (numAppointments + numBlocked + wantedAppointments) * sizeof(int) * 2, cudaMemcpyDeviceToHost);
		cudaDeviceSynchronize();

		int *d_parentMin;
		cudaMalloc(&d_parentMin, numParents * sizeof(int) * 2);
		cudaDeviceSynchronize();
		cudaMemcpy(d_parentMin, parentMin, numParents * sizeof(int) * 2, cudaMemcpyHostToDevice);
		cudaDeviceSynchronize();

		int *d_parentMax;
		cudaMalloc(&d_parentMax, numParents * sizeof(int) * 2);
		cudaDeviceSynchronize();
		cudaMemcpy(d_parentMax, parentMax, numParents * sizeof(int) * 2, cudaMemcpyHostToDevice);
		cudaDeviceSynchronize();

		int *d_fitness;
		cudaMalloc(&d_fitness, numThreads * sizeof(int) * 2);
		cudaDeviceSynchronize();
		cudaMemset(d_fitness, 0, numThreads * sizeof(int) * 2);
		cudaDeviceSynchronize();

		int *d_teacherTimes;
		cudaMalloc(&d_teacherTimes, numThreads * numSlots * sizeof(int) * 2);
		cudaDeviceSynchronize();
		cudaMemset(d_teacherTimes, 0, numThreads * numSlots * sizeof(int) * 2);
		cudaDeviceSynchronize();

		int *d_parentTimes;
		cudaMalloc(&d_parentTimes, numThreads * numSlots * sizeof(int) * 2);
		cudaDeviceSynchronize();
		cudaMemset(d_parentTimes, 0, numThreads * numSlots * sizeof(int) * 2);
		cudaDeviceSynchronize();

		cudaError_t error = cudaGetLastError();
		if (error != cudaSuccess)
		{
			fprintf(stderr, "1 ERROR: %s\n", cudaGetErrorString(error));
		}
		// generate random number
		srand(time(NULL) + Itr * 1000000);
		// rand int 1-1000000
		int seed = rand() % 1000000 + 1;
		initRand<<<blocksPerGrid, threadsPerBlock>>>(d_States, time(NULL) + Itr * 1000000 + seed);
		// print all the variables to check valid, numAppointments numThreads, legth of arrays etc
		// print legth of arrays

		// generate times

		bundledFunction<<<blocksPerGrid, threadsPerBlock, numThreads * sizeof(int)>>>(d_States, d_appTimes, d_teachers, d_teacherMin, d_teacherMax, numTeachers, d_parents, d_parentMin, d_parentMax, numParents, numSlots, numThreads, (numAppointments+ numBlocked + wantedAppointments), d_teacherTimes, d_parentTimes, numParents, numTeachers, d_fitness, numBlocked);
		cudaDeviceSynchronize();
		// wait for the kernel to finish
		cudaMemcpy(appTimes, d_appTimes, (numAppointments + numBlocked + wantedAppointments)* numThreads * sizeof(int) * 2, cudaMemcpyDeviceToHost);
		// get errors
		error = cudaGetLastError();
		if (error != cudaSuccess)
		{
			fprintf(stderr, "3 ERROR: %s\n", cudaGetErrorString(error));
		}

		// copy back fitnesses to host
		cudaMemcpy(fitness, d_fitness, numThreads * sizeof(int) * 2, cudaMemcpyDeviceToHost);
		cudaDeviceSynchronize();
		// print
		// find the best fitness
		int bestFitness = -2100000;
		int bestIndex = 0;
		for (int i = 0; i < numThreads; i++)
		{
			if (fitness[i] > bestFitness)
			{
				bestFitness = fitness[i];
				bestIndex = i;
			}
		}
		bestFitnesses[Itr] = bestFitness;
		// copy the best times to the best times array
		for (int i = 0; i < (numAppointments + numBlocked + wantedAppointments); i++)
		{
			bestTimes[Itr * (numAppointments + numBlocked + wantedAppointments) + i] = appTimes[bestIndex * (numAppointments + numBlocked + wantedAppointments) + i];
		}

		cudaFree(d_States);
		cudaFree(d_teachers);
		cudaFree(d_teacherMin);
		cudaFree(d_teacherMax);
		cudaFree(d_appTimes);
		cudaFree(d_parents);
		cudaFree(d_parentMin);
		cudaFree(d_parentMax);
		cudaFree(d_fitness);
		cudaFree(d_teacherTimes);
		cudaFree(d_parentTimes);
	}
	// free memory
	free(teacherMin);
	free(teacherMax);
	free(parentMin);
	free(parentMax);
	free(fitness);
	free(appTimes);
	free(singleTimes);
	// print best time
	int bestFitness = -2100000;
	int bestIndex = 0;
	for (int i = 0; i < iterations; i++)
	{
		if (bestFitnesses[i] > bestFitness)
		{
			bestFitness = bestFitnesses[i];
			bestIndex = i;
		}
	}
	printf("Best Fitness: %d\n", bestFitness);
	for (int i = 0; i < (numAppointments + numBlocked + wantedAppointments); i++)
	{
		printf("%d ", bestTimes[bestIndex * (numAppointments + numBlocked + wantedAppointments) + i]);
	}

	std::string outputFileName = "output.json";
	std::ofstream outputFile(outputFileName);
	nlohmann::json outputJson;
	// to write this to a file the ids need to be converted back to the original ids, and the appointments can be found by the position in the parent and teacher arrays
	for (int i = 0; i < (numAppointments + numBlocked + wantedAppointments); i++)
	{
		int teacherId = teacherMap[teachersStore[bestIndex * (numAppointments + numBlocked + wantedAppointments) + i]];
		int parentId = parentMap[parentsStore[bestIndex * (numAppointments + numBlocked + wantedAppointments) + i]];
		outputJson["appointments"][i]["teacher"] = teacherId;
		outputJson["appointments"][i]["parent"] = parentId;
		outputJson["appointments"][i]["slot"] = bestTimes[bestIndex * (numAppointments + numBlocked + wantedAppointments) + i];
	}
	outputFile << outputJson.dump(4);
	outputFile.close();


	// free memory
	free(bestTimes);
	free(bestFitnesses);
	free(teachersStore);
	free(parentsStore);
	free(teachers);
	free(parents);
	free(wantedTeachersTmp);
	free(wantedParentsTmp);
	free(teachersTmp);
	free(parentsTmp);
	return 0;
}

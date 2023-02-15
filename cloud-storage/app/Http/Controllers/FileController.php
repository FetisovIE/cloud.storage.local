<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use Illuminate\Http\JsonResponse;

class FileController extends Controller
{
    public function uploadFile(Request $request): JsonResponse
    {
        if (Auth::check()) {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = $file->getClientOriginalName();
                $directory = $request->get('directory');
                $path = Storage::disk('public')->putFile($directory, $file);

                $folder = File::where('path', $directory)->first();

                if (!$folder) {
                    $folder = new File();
                    $folder->user_id = Auth::id();
                    $folder->name = $directory;
                    $folder->path = $directory;
                    $folder->save();
                }

                $fileRecord = new File();
                $fileRecord->user_id = Auth::id();
                $fileRecord->name = $fileName;
                $fileRecord->path = $path;
                $fileRecord->folder()->associate($folder);
                $fileRecord->save();

                return response()->json(['message' => 'File successfully uploaded and stored in the database']);
            }

            return response()->json(['message' => 'File was not attached'],
                400);
        }
        return response()->json(['message' => 'User is not login'], 403);
    }

    public function makeDirectory(Request $request): JsonResponse
    {
        if (Auth::check()) {
            $directory = $request->get('directory');

            $existingDirectory = File::where('name', $directory)->first();

            if (!$existingDirectory) {
                Storage::disk('public')->makeDirectory($directory);
                $newDirectory = new File();
                $newDirectory->user_id = Auth::id();
                $newDirectory->name = $directory;
                $newDirectory->path = $directory;
                $newDirectory->save();

                return response()->json(['message' => 'Folder created successfully']);
            }

            return response()->json(
                ['message' => 'Folder with this name has already been created'],
                400);
        }
        return response()->json(['message' => 'User is not login'], 403);
    }

    public function renameDirectory(Request $request): JsonResponse
    {
        if (Auth::check()) {
            $userId = Auth::id();
            $oldDirectory = $request->get('old_name');
            $newDirectory = $request->get('new_name');
            $folders = File::where('path', 'like', "$oldDirectory%")->get();
            $names = File::where('name', 'like', "$oldDirectory%")->get();

            if (!Storage::disk('public')->exists($oldDirectory)) {
                return response()->json(
                    ['message' => "Folder $oldDirectory does not exist"], 404);
            }

            if (Storage::disk('public')->exists($newDirectory)) {
                return response()->json(
                    ['message' => "Folder $newDirectory already exists"], 400);
            }

            foreach ($folders as $folder) {
                if ($folder->user_id !== $userId) {
                    return response()->json(
                        ['message' => "Folder $oldDirectory does not belong to the user"], 403);
                }
                $newPath = str_replace($oldDirectory, $newDirectory,
                    $folder->path);
                $folder->path = $newPath;
                $folder->save();
            }

            foreach ($names as $name) {
                if ($name->user_id !== $userId) {
                    return response()->json(['message' => "File $name->name does not belong to the user"], 403);
                }
                $newName = str_replace($oldDirectory, $newDirectory,
                    $name->name);
                $name->name = $newName;
                $name->save();
            }

            Storage::disk('public')->move($oldDirectory, $newDirectory);

            return response()->json(['message' => "Folder $oldDirectory has been renamed to $newDirectory"]);
        }
        return response()->json(['message' => 'User is not login'], 403);
    }

    public function getFileInfo($id): JsonResponse
    {
        if (Auth::check()) {
            $path = File::find($id)->path;
            $userId = Auth::id();

            if (!Storage::disk('public')->exists($path)) {
                return response()->json(['message' => "File $path does not exist"],
                    404);
            }

            $fileName = File::find($id)->name;
            $size = Storage::disk('public')->size($path);
            $lastModified = File::find($id)->updated_at;
            $fileUserId = File::find($id)->user_id;

            if ($fileUserId !== $userId) {
                return response()->json(['message' => "File $fileName does not belong to the user"], 403);
            }
            return response()->json(
                [
                    'message' => "Information about the file",
                    'data' =>
                        [
                            'file name' => "$fileName",
                            'size' => $size / 1024 / 1024 . ' ' . 'MB',
                            'last modified' => "$lastModified"
                        ]
                ]
            );
        }
        return response()->json(['message' => 'User is not login'], 403);
    }

    public function getFiles(): JsonResponse
    {
        if (Auth::check()) {
            $userId = Auth::id();
            return response()->json([
                'files' => File::where('user_id', '=', $userId)->wherenotNULL('folder_id')->select('id', 'name',
                    'path')->get()
            ]);
        }
        return response()->json(['message' => 'User is not login'], 403);
    }

    public function renameOrMoveFile(Request $request): JsonResponse
    {
        if (Auth::check()) {
            $id = $request->get('id');
            $path = $request->get('path');
            $newName = $request->get('new_name');
            $newFileName = $path . DIRECTORY_SEPARATOR . $newName;
            $file = File::find($id);
            $pathFile = $file->path;
            $oldFileName = $file->name;
            $userId = Auth::id();

            if (!Storage::disk('public')->exists($pathFile)) {
                return response()->json(['message' => "File $oldFileName does not exist"], 404);
            }

            if ($file->user_id !== $userId) {
                return response()->json(['Error' => 'You do not have permission to perform this action'], 403);
            }

            $file->name = basename($newFileName);
            $folder = File::where('path', dirname($newFileName))->first();

            if ($path === dirname($pathFile)) {
                $file->save();
                return response()->json(['message' => 'File has been renamed successfully']);
            } elseif ($folder) {
                $file->path = str_replace(dirname($pathFile), dirname($newFileName), $pathFile);
                $file->folder()->associate($folder);
                $file->save();
                Storage::disk('public')->move(dirname($pathFile) . DIRECTORY_SEPARATOR . basename($pathFile),
                    dirname($newFileName) . DIRECTORY_SEPARATOR . basename($pathFile));
                return response()->json(['message' => 'File has been successfully renamed and/or moved']);
            }
            return response()->json(['message' => 'Folder to move is not found, first create a folder'], 404);
        }
        return response()->json(['message' => 'User is not login'], 403);
    }

    public function deleteFile($id): JsonResponse
    {
        if (Auth::check()) {
            $file = File::where('id', '=', $id)->whereNotNull('folder_id')->first();
            $userId = Auth::id();

            if (!$file) {
                return response()->json(['message' => "File with id $id not found or is not a file"], 404);
            }

            if ($file->user_id !== $userId) {
                return response()->json(['Error' => 'You do not have permission to perform this action'], 403);
            }

            $path = $file->path;
            $file->delete();
            Storage::disk('public')->delete($path);
            return response()->json(['message' => 'File was successfully deleted']);
        }
        return response()->json(['message' => 'User is not login'], 403);
    }

    public function getDirectoryInfo($id): JsonResponse
    {
        if (Auth::check()) {
            $directory = File::where('id', '=', $id)->whereNull('folder_id')->first();
            $userId = Auth::id();

            if (!$directory) {
                return response()->json(['message' => "Directory with id $id not found or is not a directory"], 404);
            }

            if ($directory->user_id !== $userId) {
                return response()->json(['Error' => 'You do not have permission to perform this action'], 403);
            }

            $path = $directory->path;

            if (!empty(Storage::disk('public')->files($path))) {
                return response()->json(['Files' => Storage::disk('public')->files($path)]);
            } else {
                return response()->json(['message' => "No files found in the directory"], 404);
            }
        }
        return response()->json(['message' => 'User is not login'], 403);
    }

    public function deleteDirectory($id): JsonResponse
    {
        if (Auth::check()) {
            $directory = File::where('id', '=', $id)->whereNull('folder_id')->first();
            $userId = Auth::id();

            if (!$directory) {
                return response()->json(['message' => "Directory with id $id not found or is not a directory"], 404);
            }

            if ($directory->user_id !== $userId) {
                return response()->json(['Error' => 'You do not have permission to perform this action'], 403);
            }

            $path = $directory->path;

            if (empty(Storage::disk('public')->files($path))) {
                $directory->delete();
                Storage::disk('public')->deleteDirectory($path);
                return response()->json(['message' => 'Directory was successfully deleted']);
            } else {
                return response()->json(['Error' => 'Folder is not empty, first delete the files'], 404);
            }
        }
        return response()->json(['message' => 'User is not login'], 403);
    }
}
